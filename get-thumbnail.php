<?php
/**
 * YouTube Thumbnail Downloader
 * 
 * This tool allows users to download thumbnails from YouTube videos
 * in various qualities and formats.
 */

// Include configuration
require_once 'config.php';

// Check if video_id is provided
if (!isset($_POST['video_id']) || empty($_POST['video_id'])) {
    echo '<div class="result-card">
            <div class="result-header">
                <i class="fas fa-exclamation-triangle result-icon"></i>
                <h3 class="result-title">Video ID Required</h3>
            </div>
            <div class="result-content">
                <p>Please provide a valid YouTube video ID.</p>
            </div>
          </div>';
    exit;
}

$video_id = trim($_POST['video_id']);

// Validate video ID format
if (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $video_id)) {
    echo '<div class="result-card">
            <div class="result-header">
                <i class="fas fa-exclamation-triangle result-icon"></i>
                <h3 class="result-title">Invalid Video ID</h3>
            </div>
            <div class="result-content">
                <p>The video ID must be exactly 11 characters long and contain only letters, numbers, hyphens, and underscores.</p>
            </div>
          </div>';
    exit;
}

try {
    // Get video information first
    $video_info = getVideoInfo($video_id);
    
    if ($video_info) {
        // Generate thumbnail URLs
        $thumbnails = generateThumbnailUrls($video_id);
        
        echo '<div class="result-card">
                <div class="result-header">
                    <i class="fas fa-image result-icon"></i>
                    <h3 class="result-title">Thumbnail Downloader</h3>
                </div>
                <div class="result-content">
                    <div class="result-item">
                        <div class="result-label">Video Title</div>
                        <div class="result-value">' . htmlspecialchars($video_info['title']) . '</div>
                    </div>
                    <div class="result-item">
                        <div class="result-label">Channel</div>
                        <div class="result-value">' . htmlspecialchars($video_info['channel_title']) . '</div>
                    </div>
                    <div class="result-item">
                        <div class="result-label">Video ID</div>
                        <div class="result-value">
                            <span>' . $video_id . '</span>
                            <button class="copy-btn" data-clipboard-text="' . $video_id . '">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    
                    <div class="thumbnail-grid">';
        
        foreach ($thumbnails as $quality => $url) {
            $size = getQualityInfo($quality);
            $quality_name = ucfirst($quality);
            
            echo '<div class="thumbnail-item">
                    <img src="' . $url . '" alt="' . $quality_name . ' thumbnail" class="thumbnail-image" loading="lazy">
                    <div class="thumbnail-info">
                        <div class="thumbnail-size">' . $quality_name . ' (' . $size . ')</div>
                        <a href="' . $url . '" target="_blank" class="copy-btn" style="margin-top: 8px; text-decoration: none;">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                  </div>';
        }
        
        echo '</div>
                </div>
              </div>';
    } else {
        echo '<div class="result-card">
                <div class="result-header">
                    <i class="fas fa-exclamation-triangle result-icon"></i>
                    <h3 class="result-title">Video Not Found</h3>
                </div>
                <div class="result-content">
                    <p>Could not find video with ID: <strong>' . htmlspecialchars($video_id) . '</strong></p>
                    <p>Make sure the video ID is correct and the video is publicly available.</p>
                </div>
              </div>';
    }
    
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