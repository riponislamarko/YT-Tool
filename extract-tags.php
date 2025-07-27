<?php
/**
 * YouTube Tag Extractor
 * 
 * This tool extracts tags from YouTube videos using the YouTube Data API v3.
 */

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
    // Get video data from YouTube API
    $api_key = YOUTUBE_API_KEY;
    $url = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id=" . urlencode($video_id) . "&key=" . $api_key;
    
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
                    <i class="fas fa-exclamation-triangle result-icon"></i>
                    <h3 class="result-title">Video Not Found</h3>
                </div>
                <div class="result-content">
                    <p>Could not find video with ID: <strong>' . htmlspecialchars($video_id) . '</strong></p>
                    <p>Make sure the video ID is correct and the video is publicly available.</p>
                </div>
              </div>';
        exit;
    }
    
    $video = $data['items'][0];
    $snippet = $video['snippet'];
    $tags = $snippet['tags'] ?? [];
    
    echo '<div class="result-card">
            <div class="result-header">
                <i class="fas fa-tags result-icon"></i>
                <h3 class="result-title">Tag Extractor</h3>
            </div>
            <div class="result-content">
                <div class="result-item">
                    <div class="result-label">Video Title</div>
                    <div class="result-value">' . htmlspecialchars($snippet['title']) . '</div>
                </div>
                <div class="result-item">
                    <div class="result-label">Channel</div>
                    <div class="result-value">' . htmlspecialchars($snippet['channelTitle']) . '</div>
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
                <div class="result-item">
                    <div class="result-label">Tags Found</div>
                    <div class="result-value">' . count($tags) . ' tags</div>
                </div>';
    
    if (!empty($tags)) {
        echo '<div class="tag-cloud">';
        
        foreach ($tags as $tag) {
            echo '<div class="tag-item">' . htmlspecialchars($tag) . '</div>';
        }
        
        echo '</div>
                <div class="result-item">
                    <div class="result-label">All Tags (Comma Separated)</div>
                    <div class="result-value">
                        <span>' . htmlspecialchars(implode(', ', $tags)) . '</span>
                        <button class="copy-btn" data-clipboard-text="' . htmlspecialchars(implode(', ', $tags)) . '">
                            <i class="fas fa-copy"></i> Copy All
                        </button>
                    </div>
                </div>';
    } else {
        echo '<div class="result-item">
                <div class="result-label">Tags</div>
                <div class="result-value">No tags found for this video.</div>
              </div>';
    }
    
    echo '</div>
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