<?php
/**
 * YouTube Tag Extractor
 * Extracts all tags from a YouTube video
 */

require_once 'config.php';
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Invalid Request</h5>
            <p>This endpoint only accepts POST requests.</p>
          </div>';
    exit;
}

$video_id = isset($_POST['video_id']) ? trim($_POST['video_id']) : '';

if (empty($video_id)) {
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Video ID Required</h5>
            <p>Please enter a YouTube video ID.</p>
          </div>';
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $video_id)) {
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Invalid Video ID</h5>
            <p>Video ID must be exactly 11 characters long.</p>
          </div>';
    exit;
}

if (YOUTUBE_API_KEY === 'your_youtube_api_key_here') {
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Configuration Error</h5>
            <p>YouTube API key not configured. Please update config.php with your API key.</p>
          </div>';
    exit;
}

try {
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
    
    if (isset($data['error'])) {
        $error_message = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown API error';
        throw new Exception('YouTube API Error: ' . $error_message);
    }
    
    if ($data && isset($data['items']) && !empty($data['items'])) {
        $video = $data['items'][0];
        $snippet = $video['snippet'];
        $title = htmlspecialchars($snippet['title']);
        $channel_title = htmlspecialchars($snippet['channelTitle']);
        $tags = isset($snippet['tags']) ? $snippet['tags'] : [];
        
        echo '<div class="result-container result-success">
                <h5><i class="fas fa-check-circle me-2"></i>Tags Extracted</h5>
                <p><strong>Video:</strong> ' . $title . '</p>
                <p><strong>Channel:</strong> ' . $channel_title . '</p>';
        
        if (!empty($tags)) {
            $tag_count = count($tags);
            echo '<p><strong>Total Tags:</strong> ' . $tag_count . '</p>
                  <div class="mt-3">
                    <h6><i class="fas fa-tags me-2"></i>Video Tags</h6>
                    <div class="tags-container">';
            
            foreach ($tags as $tag) {
                $escaped_tag = htmlspecialchars($tag);
                echo '<span class="tag-item">' . $escaped_tag . '</span>';
            }
            
            echo '</div>
                  <div class="mt-3">
                    <button class="btn btn-outline-primary btn-sm" onclick="copyAllTags()">
                        <i class="fas fa-copy me-2"></i>Copy All Tags
                    </button>
                  </div>';
        } else {
            echo '<div class="mt-3">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This video has no tags or tags are not publicly available.
                    </div>
                  </div>';
        }
        
        echo '<div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-link me-1"></i>
                    Video URL: <a href="https://youtube.com/watch?v=' . htmlspecialchars($video_id) . '" target="_blank">https://youtube.com/watch?v=' . htmlspecialchars($video_id) . '</a>
                </small>
              </div>
            </div>';
        
        if (!empty($tags)) {
            echo '<script>
                    function copyAllTags() {
                        const tags = ' . json_encode($tags) . ';
                        const tagText = tags.join(", ");
                        navigator.clipboard.writeText(tagText).then(() => {
                            alert("Tags copied to clipboard!");
                        });
                    }
                  </script>';
        }
        
    } else {
        echo '<div class="result-container result-error">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Video Not Found</h5>
                <p>Could not find video with ID: <strong>' . htmlspecialchars($video_id) . '</strong></p>
              </div>';
    }
    
} catch (Exception $e) {
    $error_message = DEBUG_MODE ? $e->getMessage() : 'An error occurred while processing your request.';
    
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>API Error</h5>
            <p>' . htmlspecialchars($error_message) . '</p>
          </div>';
}
?> 