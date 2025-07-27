<?php
/**
 * YouTube Video Search
 * 
 * This tool searches for YouTube videos using the YouTube Data API v3.
 */

require_once 'config.php';

// Check if keyword is provided
if (!isset($_POST['keyword']) || empty($_POST['keyword'])) {
    echo '<div class="result-card">
            <div class="result-header">
                <i class="fas fa-exclamation-triangle result-icon"></i>
                <h3 class="result-title">Keyword Required</h3>
            </div>
            <div class="result-content">
                <p>Please provide a search keyword.</p>
            </div>
          </div>';
    exit;
}

$keyword = trim($_POST['keyword']);

// Validate keyword length
if (strlen($keyword) < 2) {
    echo '<div class="result-card">
            <div class="result-header">
                <i class="fas fa-exclamation-triangle result-icon"></i>
                <h3 class="result-title">Invalid Keyword</h3>
            </div>
            <div class="result-content">
                <p>Please enter at least 2 characters for search.</p>
            </div>
          </div>';
    exit;
}

try {
    // Step 1: Search for videos to get their IDs
    $api_key = YOUTUBE_API_KEY;
    $search_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . urlencode($keyword) . "&type=video&maxResults=12&key=" . $api_key;
    
    $search_response = @file_get_contents($search_url);
    if ($search_response === false) throw new Exception('Failed to connect to YouTube API for search.');
    
    $search_data = json_decode($search_response, true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('Invalid JSON response from search API.');
    if (isset($search_data['error'])) throw new Exception('YouTube API Error: ' . $search_data['error']['message']);

    if (empty($search_data['items'])) {
        echo '<div class="video-section"><p>No videos found for: <strong>' . htmlspecialchars($keyword) . '</strong></p></div>';
        exit;
    }

    // Step 2: Get details for all found videos in a single call
    $video_ids = array_map(function($item) {
        return $item['id']['videoId'];
    }, $search_data['items']);
    
    $details_url = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics&id=" . implode(',', $video_ids) . "&key=" . $api_key;
    
    $details_response = @file_get_contents($details_url);
    if ($details_response === false) throw new Exception('Failed to connect to YouTube API for video details.');

    $details_data = json_decode($details_response, true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('Invalid JSON response from video details API.');
    if (isset($details_data['error'])) throw new Exception('YouTube API Error: ' . $details_data['error']['message']);

    // Create a map of video details for easy lookup
    $video_details_map = [];
    foreach ($details_data['items'] as $item) {
        $video_details_map[$item['id']] = $item;
    }

    // Step 3: Display the results in a modern grid
    echo '<div class="video-section search-results-container">';
    echo '<h2 class="section-title">Search Results for: "' . htmlspecialchars($keyword) . '"</h2>';
    echo '<div class="search-results-grid">';

    foreach ($search_data['items'] as $item) {
        $video_id = $item['id']['videoId'];
        $snippet = $item['snippet'];
        $details = $video_details_map[$video_id] ?? null;

        if (!$details) continue; // Skip if details weren't found

        // --- Data formatting ---
        $title = htmlspecialchars($snippet['title']);
        $channel_title = htmlspecialchars($snippet['channelTitle']);
        $thumbnail_url = $snippet['thumbnails']['high']['url'] ?? $snippet['thumbnails']['default']['url'];
        $video_url = "https://www.youtube.com/watch?v=" . $video_id;
        
        // Format view count
        $view_count = 'N/A';
        if (isset($details['statistics']['viewCount'])) {
            $raw_views = (int)$details['statistics']['viewCount'];
            if ($raw_views > 1000000) $view_count = round($raw_views / 1000000, 1) . 'M';
            elseif ($raw_views > 1000) $view_count = round($raw_views / 1000, 1) . 'K';
            else $view_count = $raw_views;
        }

        // Format duration
        $duration = 'N/A';
        if (isset($details['contentDetails']['duration'])) {
            $interval = new DateInterval($details['contentDetails']['duration']);
            if ($interval->h > 0) $duration = $interval->format('%h:%I:%S');
            else $duration = $interval->format('%i:%S');
        }

        // Format published date
        $published_date = 'N/A';
        if (isset($snippet['publishedAt'])) {
            $published_date = date('M j, Y', strtotime($snippet['publishedAt']));
        }

        echo <<<HTML
        <div class="video-card">
            <a href="{$video_url}" target="_blank" class="thumbnail-link">
                <img src="{$thumbnail_url}" alt="Thumbnail for {$title}" class="video-card-thumbnail">
                <div class="thumbnail-overlay">
                    <span class="video-duration">{$duration}</span>
                </div>
            </a>
            <div class="video-card-content">
                <h3 class="video-card-title">
                    <a href="{$video_url}" target="_blank">{$title}</a>
                </h3>
                <div class="video-card-meta">
                    <span class="channel-name">{$channel_title}</span>
                    <span class="meta-separator">•</span>
                    <span class="view-count">{$view_count} views</span>
                    <span class="meta-separator">•</span>
                    <span class="published-date">{$published_date}</span>
                </div>
            </div>
        </div>
HTML;
    }

    echo '</div></div>';

} catch (Exception $e) {
    $error_message = DEBUG_MODE ? $e->getMessage() : 'An error occurred while processing your request.';
    
    echo '<div class="video-section"><p class="error-message">' . htmlspecialchars($error_message) . '</p></div>';
}
?> 