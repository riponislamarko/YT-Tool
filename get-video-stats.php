<?php
/**
 * YouTube Detailed Video Analytics
 * 
 * This tool fetches and displays key statistics for a YouTube video.
 */

require_once 'config.php';

function get_video_id_from_input($input) {
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
        return $input;
    }
    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $input, $matches)) {
        return $matches[1];
    }
    return null;
}

function format_duration($duration) {
    $di = new DateInterval($duration);
    $str = '';
    if ($di->h > 0) $str .= $di->h . ':';
    $str .= sprintf('%02d:%02d', $di->i, $di->s);
    return $str;
}

if (!isset($_POST['video_input']) || empty($_POST['video_input'])) {
    echo '<div class="result-card error"><p>Please provide a video URL or ID.</p></div>';
    exit;
}

$input = trim($_POST['video_input']);
$api_key = YOUTUBE_API_KEY;

$video_id = get_video_id_from_input($input);

if (!$video_id) {
    echo '<div class="result-card error"><p>Invalid YouTube Video ID or URL.</p></div>';
    exit;
}

$url = "https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics,contentDetails&id=" . $video_id . "&key=" . $api_key;
$response = @file_get_contents($url);

if ($response === false) {
    echo '<div class="result-card error"><p>Error fetching data from YouTube API.</p></div>';
    exit;
}

$data = json_decode($response, true);

if (empty($data['items'])) {
    echo '<div class="result-card error"><p>Video not found or API error.</p></div>';
    exit;
}

$video = $data['items'][0];
$snippet = $video['snippet'];
$statistics = $video['statistics'];
$contentDetails = $video['contentDetails'];

$video_title = htmlspecialchars($snippet['title']);
$publish_date = date('F j, Y', strtotime($snippet['publishedAt']));
$description = nl2br(htmlspecialchars(substr($snippet['description'], 0, 280))) . '...';
$view_count = number_format($statistics['viewCount']);
$like_count = isset($statistics['likeCount']) ? number_format($statistics['likeCount']) : 'N/A';
$comment_count = isset($statistics['commentCount']) ? number_format($statistics['commentCount']) : 'N/A';
$duration = format_duration($contentDetails['duration']);

$output = "
<div class='result-card'>
    <div class='result-header'>
        <h3 class='result-title'>{$video_title}</h3>
    </div>
    <div class='result-content'>
        <div class='data-grid'>
            <div class='data-item'>
                <div class='data-label'>Views</div>
                <div class='data-value'>{$view_count}</div>
            </div>
            <div class='data-item'>
                <div class='data-label'>Likes</div>
                <div class='data-value'>{$like_count}</div>
            </div>
            <div class='data-item'>
                <div class='data-label'>Comments</div>
                <div class='data-value'>{$comment_count}</div>
            </div>
            <div class='data-item'>
                <div class='data-label'>Duration</div>
                <div class='data-value'>{$duration}</div>
            </div>
            <div class='data-item'>
                <div class='data-label'>Published</div>
                <div class='data-value'>{$publish_date}</div>
            </div>
        </div>
        <div class='video-description'>
            <h4>Description</h4>
            <p>{$description}</p>
        </div>
    </div>
</div>";

echo $output;
