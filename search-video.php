<?php
/**
 * YouTube Video Search
 * Searches for videos by keyword and returns top 5 results
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

$keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';

if (empty($keyword)) {
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Keyword Required</h5>
            <p>Please enter a search keyword.</p>
          </div>';
    exit;
}

if (strlen($keyword) > MAX_INPUT_LENGTH) {
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Keyword Too Long</h5>
            <p>Search keyword exceeds maximum allowed length.</p>
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
    $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . urlencode($keyword) . "&type=video&maxResults=5&key=" . $api_key;
    
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
        $total_results = isset($data['pageInfo']['totalResults']) ? $data['pageInfo']['totalResults'] : 0;
        
        echo '<div class="result-container result-success">
                <h5><i class="fas fa-check-circle me-2"></i>Search Results</h5>
                <p><strong>Keyword:</strong> ' . htmlspecialchars($keyword) . '</p>
                <p><strong>Total Results:</strong> ' . number_format($total_results) . ' videos found</p>
                <p><strong>Showing:</strong> Top 5 results</p>
                
                <div class="mt-3">';
        
        foreach ($data['items'] as $index => $video) {
            $snippet = $video['snippet'];
            $video_id = $video['id']['videoId'];
            $title = htmlspecialchars($snippet['title']);
            $channel_title = htmlspecialchars($snippet['channelTitle']);
            $description = htmlspecialchars(substr($snippet['description'], 0, 100)) . (strlen($snippet['description']) > 100 ? '...' : '');
            $published_at = date('M j, Y', strtotime($snippet['publishedAt']));
            $thumbnail_url = $snippet['thumbnails']['medium']['url'];
            
            echo '<div class="video-result">
                    <div class="row">
                        <div class="col-md-3">
                            <img src="' . $thumbnail_url . '" alt="Video Thumbnail" class="video-thumbnail">
                        </div>
                        <div class="col-md-9">
                            <h6 class="video-title">
                                <a href="https://youtube.com/watch?v=' . $video_id . '" target="_blank">' . $title . '</a>
                            </h6>
                            <p class="video-channel">
                                <i class="fas fa-user me-1"></i>' . $channel_title . '
                                <span class="ms-3">
                                    <i class="fas fa-calendar me-1"></i>' . $published_at . '
                                </span>
                            </p>
                            <p class="text-muted small">' . $description . '</p>
                            <div class="mt-2">
                                <button class="btn btn-outline-primary btn-sm" onclick="copyVideoId(\'' . $video_id . '\')">
                                    <i class="fas fa-copy me-1"></i>Copy Video ID
                                </button>
                                <button class="btn btn-outline-secondary btn-sm ms-2" onclick="copyVideoUrl(\'' . $video_id . '\')">
                                    <i class="fas fa-link me-1"></i>Copy URL
                                </button>
                            </div>
                        </div>
                    </div>
                  </div>';
        }
        
        echo '</div>
              </div>';
        
        echo '<script>
                function copyVideoId(videoId) {
                    navigator.clipboard.writeText(videoId).then(() => {
                        alert("Video ID copied to clipboard!");
                    });
                }
                
                function copyVideoUrl(videoId) {
                    const url = "https://youtube.com/watch?v=" + videoId;
                    navigator.clipboard.writeText(url).then(() => {
                        alert("Video URL copied to clipboard!");
                    });
                }
              </script>';
        
    } else {
        echo '<div class="result-container result-info">
                <h5><i class="fas fa-info-circle me-2"></i>No Results Found</h5>
                <p>No videos found for the keyword: <strong>' . htmlspecialchars($keyword) . '</strong></p>
                <small class="text-muted">Try using different keywords or check your spelling.</small>
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