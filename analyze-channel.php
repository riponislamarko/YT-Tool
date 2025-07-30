<?php
/**
 * All-in-One YouTube Channel Analyzer
 *
 * Fetches comprehensive data for a given YouTube channel.
 */

require_once 'config.php';

// --- Helper Functions ---

function get_channel_id_from_input($input, $api_key) {
    if (preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $input)) {
        return $input;
    }
    if (preg_match('/youtube\.com\/(?:channel\/|c\/|user\/|@)([^\/?]+)/', $input, $matches)) {
        $query = $matches[1];
        if (preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $query)) {
            return $query;
        }
    } else {
        $query = $input;
    }

    $search_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . urlencode($query) . "&type=channel&maxResults=1&key=" . $api_key;
    $response = @file_get_contents($search_url);
    if ($response === false) return null;

    $data = json_decode($response, true);
    if (empty($data['items'])) return null;

    return $data['items'][0]['snippet']['channelId'];
}

function format_number($num) {
    return number_format($num);
}

// --- Main Logic ---

if (!isset($_POST['channel_input']) || empty($_POST['channel_input'])) {
    echo '<div class="result-card error"><p>Please provide a channel URL or ID.</p></div>';
    exit;
}

$input = trim($_POST['channel_input']);
$api_key = YOUTUBE_API_KEY;

$channel_id = get_channel_id_from_input($input, $api_key);

if (!$channel_id) {
    echo '<div class="result-card error"><p>Could not find a channel with the provided input.</p></div>';
    exit;
}

// --- Fetch All Channel Data ---
$channel_url = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics,brandingSettings&id={$channel_id}&key={$api_key}";
$channel_response = @file_get_contents($channel_url);
if (!$channel_response || empty(json_decode($channel_response, true)['items'])) {
    echo '<div class="result-card error"><p>Could not retrieve channel data.</p></div>';
    exit;
}
$channel_data = json_decode($channel_response, true)['items'][0];

// --- Fetch Recent Videos ---
$videos_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&channelId={$channel_id}&order=date&type=video&maxResults=5&key={$api_key}";
$videos_response = @file_get_contents($videos_url);
$recent_videos = $videos_response ? json_decode($videos_response, true)['items'] : [];

// --- Monetization Check via Node.js Service ---
$monetization_data = [];
$node_server_url = 'http://localhost:3000/check';
$post_data = json_encode(['channelUrl' => $input]);

// Use cURL to make the request
$ch = curl_init($node_server_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($post_data)
]);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5-second connection timeout
curl_setopt($ch, CURLOPT_TIMEOUT, 45);      // 45-second total timeout for the operation

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($response !== false && $http_code === 200) {
    $monetization_data = json_decode($response, true);
} else {
    // Create an error object if the service fails
    $monetization_data = [
        'is_monetized' => false,
        'score' => 0,
        'details' => [
            'error' => $curl_error ?: "Node.js service failed with HTTP code {$http_code}"
        ]
    ];
}

// --- Prepare Data for Template ---
$snippet = $channel_data['snippet'];
$stats = $channel_data['statistics'];
$branding = $channel_data['brandingSettings'] ?? [];

// Format data for the template
$channel_data = [
    'channel_id' => $channel_id,
    'title' => $snippet['title'],
    'description' => $snippet['description'] ?? '',
    'publishedAt' => $snippet['publishedAt'],
    'country' => $snippet['country'] ?? null,
    'customUrl' => $snippet['customUrl'] ?? null,
    'defaultLanguage' => $snippet['defaultLanguage'] ?? null,
    'privacyStatus' => $snippet['privacyStatus'] ?? 'public',
    'madeForKids' => $snippet['madeForKids'] ?? false,
    'keywords' => $snippet['keywords'] ?? '',
    'thumbnail' => str_replace('=s88-c-k-c0x00ffffff-no-rj', '=s800-c-k-c0x00ffffff-no-rj', $snippet['thumbnails']['high']['url']),
    'banner' => $branding['image']['bannerExternalUrl'] ?? null,
    'subscriberCount' => (int)$stats['subscriberCount'],
    'viewCount' => (int)$stats['viewCount'],
    'videoCount' => (int)$stats['videoCount'],
    'avgViews' => $stats['videoCount'] > 0 ? (int)($stats['viewCount'] / $stats['videoCount']) : 0,
    'category' => $branding['channel']['keywords'] ?? 'Not specified',
    'recentVideos' => array_map(function($video) {
        return [
            'id' => $video['id']['videoId'] ?? '',
            'title' => $video['snippet']['title'] ?? '',
            'thumbnail' => $video['snippet']['thumbnails']['medium']['url'] ?? ''
        ];
    }, $recent_videos)
];

// Include the template file
require_once 'templates/channel_analysis.php';
