<?php
/**
 * YouTube Monetization Checker
 *
 * Checks if a YouTube video or channel is monetized.
 */

require_once 'config.php';

// --- Helper Functions ---

function get_video_id_from_input($input) {
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
        return $input;
    }
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $input, $matches)) {
        return $matches[1];
    }
    return null;
}

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

// --- Main Logic ---

if (!isset($_POST['input']) || empty($_POST['input'])) {
    echo '<div class="result-card error"><p>Please provide a channel or video URL/ID.</p></div>';
    exit;
}

$input = trim($_POST['input']);
$api_key = YOUTUBE_API_KEY;
$video_id = get_video_id_from_input($input);
$title = 'Video';

// If input is not a video, check if it's a channel
if (!$video_id) {
    $channel_id = get_channel_id_from_input($input, $api_key);
    if ($channel_id) {
        // Find the latest video from the channel
        $search_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&channelId={$channel_id}&order=date&type=video&maxResults=1&key={$api_key}";
        $response = @file_get_contents($search_url);
        if ($response) {
            $data = json_decode($response, true);
            if (!empty($data['items'])) {
                $video_id = $data['items'][0]['id']['videoId'];
                $title = $data['items'][0]['snippet']['channelTitle'] . "'s latest video";
            }
        }
    }
}

if (!$video_id) {
    echo '<div class="result-card error"><p>Could not find a video for the provided input.</p></div>';
    exit;
}

// Fetch the video watch page content
$watch_url = "https://www.youtube.com/watch?v=" . $video_id;
$page_content = @file_get_contents($watch_url);

if ($page_content === false) {
    echo '<div class="result-card error"><p>Could not fetch video page. The video may be private or deleted.</p></div>';
    exit;
}

// Check for monetization indicators in the page source
$is_monetized = preg_match('/"yt_ad"/', $page_content);

$status = $is_monetized ? 'Monetization Enabled' : 'Monetization Not Detected';
$status_class = $is_monetized ? 'status-safe' : 'status-danger';
$explanation = $is_monetized
    ? 'This video appears to be monetized. Ads are likely to be shown.'
    : 'This video does not seem to be monetized. This could be because the channel is not in the YouTube Partner Program or has disabled ads on this video.';

$output = "
<div class='result-card'>
    <div class='result-header'>
        <h3 class='result-title'>Monetization Status for {$title}</h3>
    </div>
    <div class='result-content'>
        <div class='monetization-status {$status_class}'>
            <h4>{$status}</h4>
        </div>
        <p>{$explanation}</p>
        <p class='disclaimer'>*This is an estimate based on public data and not a guarantee.</p>
    </div>
</div>";

echo $output;
