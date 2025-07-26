<?php
/**
 * YouTube Channel ID Finder
 * Finds channel ID from YouTube handle, username, or custom URL
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
$channel_input = isset($_POST['channel_input']) ? trim($_POST['channel_input']) : '';

// Validate input
if (empty($channel_input)) {
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Input Required</h5>
            <p>Please enter a YouTube handle, username, or URL.</p>
          </div>';
    exit;
}

if (strlen($channel_input) > MAX_INPUT_LENGTH) {
    echo '<div class="result-container result-error">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Input Too Long</h5>
            <p>Input exceeds maximum allowed length.</p>
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
    // Extract username/handle from input
    $username = extractUsername($channel_input);
    
    if (!$username) {
        echo '<div class="result-container result-error">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Invalid Input</h5>
                <p>Could not extract username from the provided input. Please enter a valid YouTube handle or URL.</p>
              </div>';
        exit;
    }
    
    // Get channel ID from YouTube API
    $channel_id = getChannelId($username);
    
    if ($channel_id) {
        echo '<div class="result-container result-success">
                <h5><i class="fas fa-check-circle me-2"></i>Channel Found</h5>
                <p>Successfully found the channel ID for: <strong>' . htmlspecialchars($username) . '</strong></p>
                <div class="channel-id">
                    ' . htmlspecialchars($channel_id) . '
                    <button class="copy-btn ms-2" data-clipboard-text="' . htmlspecialchars($channel_id) . '">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Channel URL: <a href="https://youtube.com/channel/' . htmlspecialchars($channel_id) . '" target="_blank">https://youtube.com/channel/' . htmlspecialchars($channel_id) . '</a>
                    </small>
                </div>
              </div>';
    } else {
        echo '<div class="result-container result-error">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Channel Not Found</h5>
                <p>Could not find a channel with the username: <strong>' . htmlspecialchars($username) . '</strong></p>
                <small class="text-muted">Make sure the username is correct and the channel exists.</small>
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
 * Extract username from various input formats
 */
function extractUsername($input) {
    // Remove whitespace and common prefixes
    $input = trim($input);
    
    // Handle full URLs
    if (preg_match('/youtube\.com\/(?:c\/|user\/|@)?([^\/\?&]+)/', $input, $matches)) {
        return $matches[1];
    }
    
    // Handle youtu.be URLs
    if (preg_match('/youtu\.be\/([^\/\?&]+)/', $input, $matches)) {
        return $matches[1];
    }
    
    // Handle @username format
    if (preg_match('/^@([a-zA-Z0-9_-]+)$/', $input, $matches)) {
        return $matches[1];
    }
    
    // Handle plain username (no @ symbol)
    if (preg_match('/^[a-zA-Z0-9_-]+$/', $input)) {
        return $input;
    }
    
    return false;
}

/**
 * Get channel ID from YouTube API
 */
function getChannelId($username) {
    $api_key = YOUTUBE_API_KEY;
    
    // Try different API endpoints
    $endpoints = [
        // Try forUsername parameter
        "https://www.googleapis.com/youtube/v3/channels?part=id&forUsername=" . urlencode($username) . "&key=" . $api_key,
        // Try search by handle
        "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . urlencode($username) . "&type=channel&maxResults=1&key=" . $api_key
    ];
    
    foreach ($endpoints as $url) {
        $response = makeApiRequest($url);
        
        if ($response && isset($response['items']) && !empty($response['items'])) {
            if (isset($response['items'][0]['id'])) {
                // Direct channel lookup
                return $response['items'][0]['id'];
            } elseif (isset($response['items'][0]['snippet']['channelId'])) {
                // Search result
                return $response['items'][0]['snippet']['channelId'];
            }
        }
    }
    
    return false;
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