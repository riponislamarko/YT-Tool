<?php
/**
 * YouTube Advanced Channel Analytics
 * 
 * This tool fetches and displays key statistics for a YouTube channel.
 */

require_once 'config.php';

// Function to extract channel ID from various input types
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

if (!isset($_POST['channel_input']) || empty($_POST['channel_input'])) {
    echo '<div class="result-card error"><p>Please provide a channel URL or ID.</p></div>';
    exit;
}

$input = trim($_POST['channel_input']);
$api_key = YOUTUBE_API_KEY;

$channel_id = get_channel_id_from_input($input, $api_key);

if (!$channel_id) {
    echo '<div class="result-card error"><p>Could not find a channel with the provided input. Please check the channel URL, ID, or handle.</p></div>';
    exit;
}

$url = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id=" . $channel_id . "&key=" . $api_key;
$response = @file_get_contents($url);

if ($response === false) {
    echo '<div class="result-card error"><p>Error fetching data from YouTube API.</p></div>';
    exit;
}

$data = json_decode($response, true);

if (empty($data['items'])) {
    echo '<div class="result-card error"><p>Channel not found or API error.</p></div>';
    exit;
}

$channel = $data['items'][0];
$snippet = $channel['snippet'];
$statistics = $channel['statistics'];

$channel_title = htmlspecialchars($snippet['title']);
$thumbnail_url = htmlspecialchars($snippet['thumbnails']['default']['url']);
$subscriber_count = isset($statistics['subscriberCount']) ? number_format($statistics['subscriberCount']) : 'N/A (Hidden)';
$view_count = number_format($statistics['viewCount']);
$video_count = number_format($statistics['videoCount']);

$output = "
<div class='result-card'>
    <div class='result-header'>
        <img src='{$thumbnail_url}' alt='Channel Thumbnail' class='channel-thumb'>
        <h3 class='result-title'>{$channel_title}</h3>
    </div>
    <div class='result-content'>
        <div class='data-grid'>
            <div class='data-item'>
                <div class='data-label'>Subscribers</div>
                <div class='data-value'>{$subscriber_count}</div>
            </div>
            <div class='data-item'>
                <div class='data-label'>Total Views</div>
                <div class='data-value'>{$view_count}</div>
            </div>
            <div class='data-item'>
                <div class='data-label'>Total Videos</div>
                <div class='data-value'>{$video_count}</div>
            </div>
        </div>
    </div>
</div>";

echo $output;
