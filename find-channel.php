<?php
/**
 * YouTube Channel Finder
 * 
 * This tool helps find YouTube channel IDs from various input formats.
 */

// Include configuration
require_once 'config.php';

// Check if input is provided
if (!isset($_POST['input']) || empty($_POST['input'])) {
    echo '<div class="result-card">
            <div class="result-header">
                <i class="fas fa-exclamation-triangle result-icon"></i>
                <h3 class="result-title">Input Required</h3>
            </div>
            <div class="result-content">
                <p>Please provide a channel URL, channel ID, or channel handle.</p>
            </div>
          </div>';
    exit;
}

$input = trim($_POST['input']);

try {
    $channel_id = null;
    $channel_handle = null;
    $channel_title = null;
    $channel_url = null;
    
    // Check if it's already a channel ID
    if (preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $input)) {
        $channel_id = $input;
        $channel_url = "https://www.youtube.com/channel/{$channel_id}";
    }
    // Check if it's a channel handle
    elseif (preg_match('/^@([a-zA-Z0-9_-]+)$/', $input, $matches)) {
        $channel_handle = $matches[1];
        $channel_url = "https://www.youtube.com/@{$channel_handle}";
    }
    // Check if it's a channel URL
    elseif (preg_match('/youtube\.com\/(?:channel\/|c\/|user\/|@)([^\/\?]+)/', $input, $matches)) {
        $handle_or_id = $matches[1];
        
        if (preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $handle_or_id)) {
            $channel_id = $handle_or_id;
            $channel_url = "https://www.youtube.com/channel/{$channel_id}";
        } else {
            $channel_handle = $handle_or_id;
            $channel_url = "https://www.youtube.com/@{$channel_handle}";
        }
    }
    // Check if it's just a handle without @
    elseif (preg_match('/^[a-zA-Z0-9_-]+$/', $input)) {
        $channel_handle = $input;
        $channel_url = "https://www.youtube.com/@{$channel_handle}";
    }
    
    if (!$channel_id && !$channel_handle) {
        echo '<div class="result-card">
                <div class="result-header">
                    <i class="fas fa-exclamation-triangle result-icon"></i>
                    <h3 class="result-title">Invalid Input</h3>
                </div>
                <div class="result-content">
                    <p>Please provide a valid channel URL, channel ID, or channel handle.</p>
                    <p>Examples:</p>
                    <ul>
                        <li>Channel ID: UC_x5XG1OV2P6uZZ5FSM9Ttw</li>
                        <li>Channel Handle: @GoogleDevelopers</li>
                        <li>Channel URL: https://www.youtube.com/channel/UC_x5XG1OV2P6uZZ5FSM9Ttw</li>
                    </ul>
                </div>
              </div>';
        exit;
    }
    
    // Get channel information from YouTube API
    $api_key = YOUTUBE_API_KEY;
    
    if ($channel_id) {
        $url = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id=" . urlencode($channel_id) . "&key=" . $api_key;
    } else {
        $url = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&forHandle=@" . urlencode($channel_handle) . "&key=" . $api_key;
    }
    
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
                    <h3 class="result-title">Channel Not Found</h3>
                </div>
                <div class="result-content">
                    <p>Could not find channel with the provided information.</p>
                    <p>Make sure the channel exists and is publicly accessible.</p>
                </div>
              </div>';
        exit;
    }
    
    $channel = $data['items'][0];
    $snippet = $channel['snippet'];
    $statistics = $channel['statistics'];
    
    $channel_id = $channel['id'];
    $channel_title = $snippet['title'];
    $subscriber_count = number_format($statistics['subscriberCount'] ?? 0);
    $view_count = number_format($statistics['viewCount'] ?? 0);
    $video_count = number_format($statistics['videoCount'] ?? 0);
    
    echo '<div class="result-card">
            <div class="result-header">
                <i class="fas fa-user result-icon"></i>
                <h3 class="result-title">Channel Information</h3>
            </div>
            <div class="result-content">
                <div class="data-grid">
                    <div class="data-item">
                        <div class="data-label">Channel Name</div>
                        <div class="data-value">' . htmlspecialchars($channel_title) . '</div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Channel ID</div>
                        <div class="data-value">
                            <span>' . $channel_id . '</span>
                            <button class="copy-btn" data-clipboard-text="' . $channel_id . '">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Subscribers</div>
                        <div class="data-value">' . $subscriber_count . '</div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Total Views</div>
                        <div class="data-value">' . $view_count . '</div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Videos</div>
                        <div class="data-value">' . $video_count . '</div>
                    </div>
                    <div class="data-item">
                        <div class="data-label">Created</div>
                        <div class="data-value">' . date('F j, Y', strtotime($snippet['publishedAt'])) . '</div>
                    </div>
                </div>
                <div class="result-item">
                    <div class="result-label">Channel URL</div>
                    <div class="result-value">
                        <a href="https://www.youtube.com/channel/' . $channel_id . '" target="_blank">https://www.youtube.com/channel/' . $channel_id . '</a>
                        <button class="copy-btn" data-clipboard-text="https://www.youtube.com/channel/' . $channel_id . '">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
                <div class="result-item">
                    <div class="result-label">Description</div>
                    <div class="result-value">' . nl2br(htmlspecialchars(substr($snippet['description'], 0, 500))) . (strlen($snippet['description']) > 500 ? '...' : '') . '</div>
                </div>
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