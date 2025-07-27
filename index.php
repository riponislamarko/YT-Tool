<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="YouTube Toolkit - Free tools for YouTube creators">
    <meta name="keywords" content="YouTube, toolkit, data viewer, channel analysis, video tools">
    <meta name="author" content="YouTube Toolkit">
    <meta name="theme-color" content="#ffffff">
    
    <title>YouTube Toolkit - Free YouTube Tools</title>
    
    <!-- Preload critical resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <link href="assets/style.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎥</text></svg>">
</head>
<body>
    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Main Container -->
    <div class="container" id="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1 class="logo">
                    <i class="fas fa-play-circle"></i>
                    <span>YouTube Toolkit</span>
                </h1>
                <p class="tagline">Free tools for YouTube creators</p>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Search Section -->
            <section class="search-section">
                <div class="search-container">
                    <div class="search-box">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   id="youtubeInput" 
                                   class="search-input" 
                                   placeholder="Enter YouTube URL, video ID, channel ID, or channel handle"
                                   autocomplete="off">
                        </div>
                        <div class="tool-selector">
                            <button class="tool-btn active" data-tool="data">
                                <i class="fas fa-chart-bar"></i>
                                <span>Data Viewer</span>
                            </button>
                            <button class="tool-btn" data-tool="channel">
                                <i class="fas fa-search"></i>
                                <span>Channel Finder</span>
                            </button>
                            <button class="tool-btn" data-tool="thumbnail">
                                <i class="fas fa-image"></i>
                                <span>Thumbnails</span>
                            </button>
                            <button class="tool-btn" data-tool="tags">
                                <i class="fas fa-tags"></i>
                                <span>Tags</span>
                            </button>
                            <button class="tool-btn" data-tool="search">
                                <i class="fas fa-video"></i>
                                <span>Search</span>
                            </button>
                        </div>
                        <button class="analyze-btn" id="searchBtn">
                            <i class="fas fa-play"></i>
                            <span>Analyze</span>
                        </button>
                    </div>
                </div>
                
                <!-- Status Messages -->
                <div class="status-container">
                    <div class="status-message success" id="successMessage" style="display: none;">
                        <i class="fas fa-check-circle"></i>
                        <span>Success!</span>
                    </div>
                    <div class="status-message error" id="errorMessage" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Please enter a valid YouTube URL, video ID, channel ID, or channel handle.</span>
                    </div>
                </div>
            </section>
            
            <!-- Results Section -->
            <section class="results-section">
                <div class="results-container" id="resultsContainer" style="display: none;">
                    <div class="results-content" id="resultsContent"></div>
                </div>
            </section>
        </main>
        
        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-links">
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <a href="#" class="footer-link">Terms of Service</a>
                    <a href="#" class="footer-link">Contact</a>
                </div>
                <div class="footer-copyright">
                    <p>&copy; 2024 YouTube Toolkit. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Custom JavaScript -->
    <script src="assets/script.js"></script>
</body>
</html>

<?php
// Data Viewer Functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url']) && isset($_POST['tool']) && $_POST['tool'] === 'data') {
    require_once 'config.php';
    
    $url = trim($_POST['url']);
    
    if (empty($url)) {
        echo '<div class="result-card">
                <div class="result-header">
                    <i class="fas fa-exclamation-triangle result-icon"></i>
                    <h3 class="result-title">Input Required</h3>
                </div>
                <div class="result-content">
                    <p>Please enter a valid YouTube URL, video ID, channel ID, or channel handle.</p>
                </div>
              </div>';
        exit;
    }
    
    // Extract video ID or channel ID from URL
    $videoId = null;
    $channelId = null;
    $channelHandle = null;
    
    // Check if it's a video URL
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        $videoId = $matches[1];
    } elseif (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
        $videoId = $url;
    }
    
    // Check if it's a channel URL
    if (preg_match('/youtube\.com\/(?:channel\/|c\/|user\/|@)([^\/\?]+)/', $url, $matches)) {
        $channelHandle = $matches[1];
        if (preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $channelHandle)) {
            $channelId = $channelHandle;
            $channelHandle = null;
        }
    } elseif (preg_match('/^@([a-zA-Z0-9_-]+)$/', $url, $matches)) {
        $channelHandle = $matches[1];
    } elseif (preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $url)) {
        $channelId = $url;
    }
    
    try {
        if ($videoId) {
            // Get video data
            $videoUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics,contentDetails&id={$videoId}&key=" . YOUTUBE_API_KEY;
            $videoResponse = file_get_contents($videoUrl);
            $videoData = json_decode($videoResponse, true);
            
            if (empty($videoData['items'])) {
                echo '<div class="result-card">
                        <div class="result-header">
                            <i class="fas fa-exclamation-triangle result-icon"></i>
                            <h3 class="result-title">Video Not Found</h3>
                        </div>
                        <div class="result-content">
                            <p>The video could not be found. Please check the video ID or URL.</p>
                        </div>
                      </div>';
                exit;
            }
            
            $video = $videoData['items'][0];
            $snippet = $video['snippet'];
            $statistics = $video['statistics'];
            $contentDetails = $video['contentDetails'];
            
            // Format numbers
            $viewCount = number_format($statistics['viewCount'] ?? 0);
            $likeCount = number_format($statistics['likeCount'] ?? 0);
            $commentCount = number_format($statistics['commentCount'] ?? 0);
            $duration = $contentDetails['duration'];
            
            // Convert ISO 8601 duration to readable format
            preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $matches);
            $hours = isset($matches[1]) ? intval($matches[1]) : 0;
            $minutes = isset($matches[2]) ? intval($matches[2]) : 0;
            $seconds = isset($matches[3]) ? intval($matches[3]) : 0;
            $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            
            echo '<div class="result-card">
                    <div class="result-header">
                        <i class="fas fa-video result-icon"></i>
                        <h3 class="result-title">Video Data</h3>
                    </div>
                    <div class="result-content">
                        <div class="data-grid">
                            <div class="data-item">
                                <div class="data-label">Title</div>
                                <div class="data-value">' . htmlspecialchars($snippet['title']) . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Channel</div>
                                <div class="data-value">' . htmlspecialchars($snippet['channelTitle']) . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Published</div>
                                <div class="data-value">' . date('F j, Y', strtotime($snippet['publishedAt'])) . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Duration</div>
                                <div class="data-value">' . $formattedDuration . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Views</div>
                                <div class="data-value">' . $viewCount . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Likes</div>
                                <div class="data-value">' . $likeCount . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Comments</div>
                                <div class="data-value">' . $commentCount . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Video ID</div>
                                <div class="data-value">
                                    <span>' . $videoId . '</span>
                                    <button class="copy-btn" data-clipboard-text="' . $videoId . '">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="result-item">
                            <div class="result-label">Description</div>
                            <div class="result-value">' . nl2br(htmlspecialchars(substr($snippet['description'], 0, 500))) . (strlen($snippet['description']) > 500 ? '...' : '') . '</div>
                        </div>
                        <div class="result-item">
                            <div class="result-label">Video URL</div>
                            <div class="result-value">
                                <a href="https://www.youtube.com/watch?v=' . $videoId . '" target="_blank">https://www.youtube.com/watch?v=' . $videoId . '</a>
                                <button class="copy-btn" data-clipboard-text="https://www.youtube.com/watch?v=' . $videoId . '">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>
                  </div>';
                  
        } elseif ($channelId || $channelHandle) {
            // Get channel data
            $channelParam = $channelId ? "id={$channelId}" : "forHandle=@{$channelHandle}";
            $channelUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics,brandingSettings&{$channelParam}&key=" . YOUTUBE_API_KEY;
            $channelResponse = file_get_contents($channelUrl);
            $channelData = json_decode($channelResponse, true);
            
            if (empty($channelData['items'])) {
                echo '<div class="result-card">
                        <div class="result-header">
                            <i class="fas fa-exclamation-triangle result-icon"></i>
                            <h3 class="result-title">Channel Not Found</h3>
                        </div>
                        <div class="result-content">
                            <p>The channel could not be found. Please check the channel ID or handle.</p>
                        </div>
                      </div>';
                exit;
            }
            
            $channel = $channelData['items'][0];
            $snippet = $channel['snippet'];
            $statistics = $channel['statistics'];
            $branding = $channel['brandingSettings'];
            
            // Format numbers
            $subscriberCount = number_format($statistics['subscriberCount'] ?? 0);
            $viewCount = number_format($statistics['viewCount'] ?? 0);
            $videoCount = number_format($statistics['videoCount'] ?? 0);
            
            echo '<div class="result-card">
                    <div class="result-header">
                        <i class="fas fa-user result-icon"></i>
                        <h3 class="result-title">Channel Data</h3>
                    </div>
                    <div class="result-content">
                        <div class="data-grid">
                            <div class="data-item">
                                <div class="data-label">Channel Name</div>
                                <div class="data-value">' . htmlspecialchars($snippet['title']) . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Subscribers</div>
                                <div class="data-value">' . $subscriberCount . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Total Views</div>
                                <div class="data-value">' . $viewCount . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Videos</div>
                                <div class="data-value">' . $videoCount . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Created</div>
                                <div class="data-value">' . date('F j, Y', strtotime($snippet['publishedAt'])) . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Country</div>
                                <div class="data-value">' . ($snippet['country'] ?? 'Not specified') . '</div>
                            </div>
                            <div class="data-item">
                                <div class="data-label">Channel ID</div>
                                <div class="data-value">
                                    <span>' . $channel['id'] . '</span>
                                    <button class="copy-btn" data-clipboard-text="' . $channel['id'] . '">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="result-item">
                            <div class="result-label">Description</div>
                            <div class="result-value">' . nl2br(htmlspecialchars(substr($snippet['description'], 0, 500))) . (strlen($snippet['description']) > 500 ? '...' : '') . '</div>
                        </div>
                        <div class="result-item">
                            <div class="result-label">Channel URL</div>
                            <div class="result-value">
                                <a href="https://www.youtube.com/channel/' . $channel['id'] . '" target="_blank">https://www.youtube.com/channel/' . $channel['id'] . '</a>
                                <button class="copy-btn" data-clipboard-text="https://www.youtube.com/channel/' . $channel['id'] . '">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                    </div>
                  </div>';
        } else {
            echo '<div class="result-card">
                    <div class="result-header">
                        <i class="fas fa-exclamation-triangle result-icon"></i>
                        <h3 class="result-title">Invalid Input</h3>
                    </div>
                    <div class="result-content">
                        <p>Please enter a valid YouTube video URL, video ID, channel URL, channel ID, or channel handle.</p>
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
}
?> 