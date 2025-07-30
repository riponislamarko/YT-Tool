<?php
/**
 * YouTube Image Downloader
 * 
 * This tool fetches a channel's profile picture and banner image.
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
    echo '<div class="result-card error"><p>Could not find a channel with the provided input.</p></div>';
    exit;
}

$url = "https://www.googleapis.com/youtube/v3/channels?part=snippet,brandingSettings&id=" . $channel_id . "&key=" . $api_key;
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
$branding = $channel['brandingSettings'];

$profile_pic_url = str_replace('=s88-c-k-c0x00ffffff-no-rj', '=s800-c-k-c0x00ffffff-no-rj', $snippet['thumbnails']['high']['url']);
$banner_url = isset($branding['image']['bannerExternalUrl']) ? $branding['image']['bannerExternalUrl'] . '=w2120-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj' : null;

$output = "
<div class='result-card'>
    <div class='result-header'>
        <h3 class='result-title'>Channel Images for " . htmlspecialchars($snippet['title']) . "</h3>
    </div>
    <div class='result-content image-results'>
        <div class='image-container'>
            <h4>Profile Picture</h4>
            <img src='" . htmlspecialchars($profile_pic_url) . "' alt='Profile Picture'>
            <a href='" . htmlspecialchars($profile_pic_url) . "' download class='download-btn'>Download HD</a>
        </div>";

if ($banner_url) {
    $output .= "
        <div class='image-container'>
            <h4>Channel Banner</h4>
            <img src='" . htmlspecialchars($banner_url) . "' alt='Channel Banner'>
            <a href='" . htmlspecialchars($banner_url) . "' download class='download-btn'>Download HD</a>
        </div>";
} else {
    $output .= "
        <div class='image-container'>
            <h4>Channel Banner</h4>
            <p>This channel does not have a banner image.</p>
        </div>";
}

$output .= "
    </div>
</div>";

echo $output;
