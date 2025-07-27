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
    // Search for videos using YouTube API
    $api_key = YOUTUBE_API_KEY;
    $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . urlencode($keyword) . "&type=video&maxResults=10&key=" . $api_key;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => REQUEST_TIMEOUT,
            'user_agent' => 'YouTube-Utility-Tool/1.0'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        throw new Exception('Failed to connect to YouTube API');
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from API');
    }
    
    // Check for API errors
    if (isset($data['error'])) {
        $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown API error';
        throw new Exception('YouTube API Error: ' . $error_message);
    }
    
    if (empty($data['items'])) {
        echo '<div class="result-card">
                <div class="result-header">
                    <i class="fas fa-search result-icon"></i>
                    <h3 class="result-title">No Results Found</h3>
                </div>
                <div class="result-content">
                    <p>No videos found for: <strong>' . htmlspecialchars($keyword) . '</strong></p>
                    <p>Try different keywords or check your spelling.</p>
                </div>
              </div>';
        exit;
    }
    
    $total_results = $data['pageInfo']['totalResults'] ?? 0;
    $results_per_page = $data['pageInfo']['resultsPerPage'] ?? 0;
    
    echo '<div class="result-card">
            <div class="result-header">
                <i class="fas fa-search result-icon"></i>
                <h3 class="result-title">Search Results</h3>
            </div>
            <div class="result-content">
                <div class="result-item">
                    <div class="result-label">Search Term</div>
                    <div class="result-value">' . htmlspecialchars($keyword) . '</div>
                </div>
                <div class="result-item">
                    <div class="result-label">Results Found</div>
                    <div class="result-value">' . number_format($total_results) . ' videos</div>
                </div>
                
                <div class="search-results">';
    
    foreach ($data['items'] as $item) {
        $video_id = $item['id']['videoId'];
        $snippet = $item['snippet'];
        $title = htmlspecialchars($snippet['title']);
        $channel_title = htmlspecialchars($snippet['channelTitle']);
        $published_date = date('M j, Y', strtotime($snippet['publishedAt']));
        $description = htmlspecialchars(substr($snippet['description'], 0, 150));
        $thumbnail_url = $snippet['thumbnails']['medium']['url'] ?? $snippet['thumbnails']['default']['url'];
        
        echo '<div class="search-item">
                <div class="search-item-header">
                    <img src="' . $thumbnail_url . '" alt="Video thumbnail" class="search-thumbnail">
                    <div class="search-item-content">
                        <div class="search-item-title">
                            <a href="https://www.youtube.com/watch?v=' . $video_id . '" target="_blank">' . $title . '</a>
                        </div>
                        <div class="search-item-channel">' . $channel_title . '</div>
                        <div class="search-item-meta">
                            Published: ' . $published_date . ' • 
                            Video ID: ' . $video_id . '
                            <button class="copy-btn" data-clipboard-text="' . $video_id . '" style="margin-left: 8px;">
                                <i class="fas fa-copy"></i> Copy ID
                            </button>
                        </div>
                        <div class="search-item-meta">' . $description . (strlen($snippet['description']) > 150 ? '...' : '') . '</div>
                    </div>
                </div>
              </div>';
    }
    
    echo '</div>
            </div>
          </div>';
    
} catch (Exception $e) {
    $error_message = DEBUG_MODE ? $e->getMessage() : 'An error occurred while processing your request.';
    
    echo '<div class="result-card">
            <div class="result-header">
                <i class="fas fa-exclamation-triangle result-icon"></i>
                <h3 class="result-title">Error</h3>
            </div>
            <div class="result-content">
                <p>' . htmlspecialchars($error_message) . '</p>
            </div>
          </div>';
}
?> 