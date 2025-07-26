<?php
/**
 * YouTube Thumbnail Downloader
 * Gets thumbnail download links for a YouTube video ID
 */

// Include configuration
require_once 'config.php';

// Set headers for AJAX response
header('Content-Type: text/html; charset=utf-8');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Invalid Request</h5>
            <p>This endpoint only accepts POST requests.</p>
          </div>';
    exit;
}

// Get and sanitize input
$video_id = isset($_POST['video_id']) ? trim($_POST['video_id']) : '';

// Validate input
if (empty($video_id)) {
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Video ID Required</h5>
            <p>Please enter a YouTube video ID.</p>
          </div>';
    exit;
}

// Validate video ID format
if (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $video_id)) {
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Invalid Video ID</h5>
            <p>Video ID must be exactly 11 characters long and contain only letters, numbers, hyphens, and underscores.</p>
            <small class="text-muted">Example: dQw4w9WgXcQ</small>
          </div>';
    exit;
}

// Check if API key is configured
if (YOUTUBE_API_KEY === 'your_youtube_api_key_here') {
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Configuration Error</h5>
            <p>YouTube API key not configured. Please update config.php with your API key.</p>
            <small class="text-muted">Get your API key from: <a href="https://console.developers.google.com/apis/credentials" target="_blank">Google Cloud Console</a></small>
          </div>';
    exit;
}

try {
    // Get video information from YouTube API
    $video_info = getVideoInfo($video_id);
    
    if ($video_info) {
        $title = htmlspecialchars($video_info['title']);
        $channel_title = htmlspecialchars($video_info['channel_title']);
        
        // Generate thumbnail URLs
        $thumbnails = generateThumbnailUrls($video_id);
        
        echo '<div class="result-container result-success">
                <h5><i class="fas fa-check-circle me-2"></i>Thumbnails Found</h5>
                <p><strong>Video:</strong> ' . $title . '</p>
                <p><strong>Channel:</strong> ' . $channel_title . '</p>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6><i class="fas fa-image me-2"></i>Thumbnail Preview</h6>
                        <img src="' . $thumbnails['maxres'] . '" alt="Video Thumbnail" class="thumbnail-preview" onerror="this.src=\'' . $thumbnails['high'] . '\'">
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-download me-2"></i>Download Links</h6>
                        <div class="d-grid gap-2">';
        
        // Display download links for each quality
        foreach ($thumbnails as $quality => $url) {
            $quality_label = ucfirst($quality);
            $quality_info = getQualityInfo($quality);
            
            echo '<a href="' . $url . '" target="_blank" class="thumbnail-link">
                    <i class="fas fa-download me-2"></i>' . $quality_label . ' (' . $quality_info . ')
                    <button class="copy-btn ms-2" data-clipboard-text="' . $url . '">
                        <i class="fas fa-copy"></i>
                    </button>
                  </a>';
        }
        
        echo '</div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Video URL: <a href="https://youtube.com/watch?v=' . htmlspecialchars($video_id) . '" target="_blank">https://youtube.com/watch?v=' . htmlspecialchars($video_id) . '</a>
                    </small>
                </div>
              </div>';
    } else {
        echo '<div class="result-container result-error">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Video Not Found</h5>
                <p>Could not find video with ID: <strong>' . htmlspecialchars($video_id) . '</strong></p>
                <small class="text-muted">Make sure the video ID is correct and the video is publicly available.</small>
              </div>';
    }
    
} catch (Exception $e) {
    $error_message = DEBUG_MODE ? $e->getMessage() : 'An error occurred while processing your request.';
    
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>API Error</h5>
            <p>' . htmlspecialchars($error_message) . '</p>
            <small class="text-muted">Please try again later or check your API key configuration.</small>
          </div>';
}

/**
 * Get video information from YouTube API
 */
function getVideoInfo($video_id) {
    $api_key = YOUTUBE_API_KEY;
    $url = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id=" . urlencode($video_id) . "&key=" . $api_key;
    
    $response = makeApiRequest($url);
    
    if ($response && isset($response['items']) && !empty($response['items'])) {
        $video = $response['items'][0];
        return [
            'title' => $video['snippet']['title'],
            'channel_title' => $video['snippet']['channelTitle']
        ];
    }
    
    return false;
}

/**
 * Generate thumbnail URLs for different qualities
 */
function generateThumbnailUrls($video_id) {
    return [
        'default' => "https://img.youtube.com/vi/{$video_id}/default.jpg",
        'medium' => "https://img.youtube.com/vi/{$video_id}/mqdefault.jpg",
        'high' => "https://img.youtube.com/vi/{$video_id}/hqdefault.jpg",
        'standard' => "https://img.youtube.com/vi/{$video_id}/sddefault.jpg",
        'maxres' => "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg"
    ];
}

/**
 * Get quality information for thumbnails
 */
function getQualityInfo($quality) {
    $info = [
        'default' => '120x90',
        'medium' => '320x180',
        'high' => '480x360',
        'standard' => '640x480',
        'maxres' => '1280x720'
    ];
    
    return isset($info[$quality]) ? $info[$quality] : 'Unknown';
}

/**
 * Make API request with error handling
 */
function makeApiRequest($url) {
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
    
    return $data;
}
?> 