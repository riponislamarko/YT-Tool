<?php
/**
 * YouTube Earnings Calculator
 *
 * Estimates earnings for a YouTube channel or video.
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

function calculate_earnings($view_count) {
    $low_rpm = 1.5; // Lower end RPM
    $high_rpm = 4.0; // Higher end RPM

    $estimated_low = ($view_count / 1000) * $low_rpm;
    $estimated_high = ($view_count / 1000) * $high_rpm;

    return [
        'low' => number_format($estimated_low, 2),
        'high' => number_format($estimated_high, 2)
    ];
}

// --- Main Logic ---

if (!isset($_POST['input']) || empty($_POST['input'])) {
    echo '<div class="result-card error"><p>Please provide a channel or video URL/ID.</p></div>';
    exit;
}

$input = trim($_POST['input']);
$api_key = YOUTUBE_API_KEY;

$video_id = get_video_id_from_input($input);
$channel_id = null;
$view_count = 0;
$title = '';
$type = '';

if ($video_id) {
    // It's a video
    $url = "https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics&id=" . $video_id . "&key=" . $api_key;
    $response = @file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        if (!empty($data['items'])) {
            $video = $data['items'][0];
            $view_count = $video['statistics']['viewCount'];
            $title = $video['snippet']['title'];
            $type = 'Video';
        }
    }
} else {
    // Assume it's a channel
    $channel_id = get_channel_id_from_input($input, $api_key);
    if ($channel_id) {
        $url = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id=" . $channel_id . "&key=" . $api_key;
        $response = @file_get_contents($url);
        if ($response) {
            $data = json_decode($response, true);
            if (!empty($data['items'])) {
                $channel = $data['items'][0];
                $view_count = $channel['statistics']['viewCount'];
                $title = $channel['snippet']['title'];
                $type = 'Channel';
            }
        }
    }
}

if ($view_count == 0 || empty($title)) {
    echo '<div class="result-card error"><p>Could not find a channel or video with the provided input.</p></div>';
    exit;
}

$earnings = calculate_earnings($view_count);

$output = "
<div class='result-card'>
    <div class='result-header'>
        <h3 class='result-title'>Earnings Estimate for " . htmlspecialchars($title) . "</h3>
        <span class='result-subtitle'>({$type})</span>
    </div>
    <div class='result-content'>
        <div class='data-grid earnings-grid'>
            <div class='data-item'>
                <div class='data-label'>Total Views</div>
                <div class='data-value'>" . number_format($view_count) . "</div>
            </div>
            <div class='data-item'>
                <div class='data-label'>Estimated Earnings (Low)</div>
                <div class='data-value earnings-low'>$" . $earnings['low'] . "</div>
            </div>
            <div class='data-item'>
                <div class='data-label'>Estimated Earnings (High)</div>
                <div class='data-value earnings-high'>$" . $earnings['high'] . "</div>
            </div>
        </div>
        <p class='disclaimer'>*Estimates are based on a standard RPM range of $1.50 - $4.00. Actual earnings may vary.</p>
    </div>
</div>";

echo $output;
