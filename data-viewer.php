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
            $videoUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics,contentDetails,player&id={$videoId}&key=" . YOUTUBE_API_KEY;
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
            $player = $video['player'];
            $thumbnailUrl = $snippet['thumbnails']['maxres']['url'] ?? $snippet['thumbnails']['high']['url'];
            $videoUrl = 'https://www.youtube.com/watch?v=' . $videoId;

            // Get channel data for thumbnail and subscriber count
            $channelIdForThumb = $snippet['channelId'];
            $channelUrlForThumb = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id={$channelIdForThumb}&key=" . YOUTUBE_API_KEY;
            $channelResponseThumb = file_get_contents($channelUrlForThumb);
            $channelDataThumb = json_decode($channelResponseThumb, true);
            $channelThumbnail = !empty($channelDataThumb['items'][0]['snippet']['thumbnails']['default']['url']) ? $channelDataThumb['items'][0]['snippet']['thumbnails']['default']['url'] : '';
            $subscriberCount = !empty($channelDataThumb['items'][0]['statistics']['subscriberCount']) ? number_format($channelDataThumb['items'][0]['statistics']['subscriberCount']) : 'N/A';


            // Format numbers
            $viewCountNum = (int)($statistics['viewCount'] ?? 0);
            $likeCountNum = (int)($statistics['likeCount'] ?? 0);
            $commentCountNum = (int)($statistics['commentCount'] ?? 0);

            $viewCount = number_format($viewCountNum);
            $likeCount = number_format($likeCountNum);
            $commentCount = number_format($commentCountNum);
            $duration = $contentDetails['duration'];

            // --- Revenue Estimation ---
            $rpmLow = 1.5; // Lower end of RPM range
            $rpmHigh = 4.0; // Higher end of RPM range
            $estimatedRevenueLow = ($viewCountNum / 1000) * $rpmLow;
            $estimatedRevenueHigh = ($viewCountNum / 1000) * $rpmHigh;

            // --- Performance Ratios ---
            $likesPer1000Views = $viewCountNum > 0 ? round(($likeCountNum / $viewCountNum) * 1000, 2) : 0;
            $commentsPer1000Views = $viewCountNum > 0 ? round(($commentCountNum / $viewCountNum) * 1000, 2) : 0;
            $likeToCommentRatio = $commentCountNum > 0 ? round($likeCountNum / $commentCountNum, 2) : 0;
            
            // Convert ISO 8601 duration to readable format
            preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $matches);
            $hours = isset($matches[1]) ? intval($matches[1]) : 0;
            $minutes = isset($matches[2]) ? intval($matches[2]) : 0;
            $seconds = isset($matches[3]) ? intval($matches[3]) : 0;
            $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            $publishedDate = date('F j, Y', strtotime($snippet['publishedAt']));

            // Start of new layout
            echo <<<HTML
            <div class="video-data-container">
                <header class="video-section video-header">
                    <h2 class="video-title">{$snippet['title']}</h2>
                    <div class="channel-info">
                        <img src="{$channelThumbnail}" alt="Channel Thumbnail" class="channel-thumbnail">
                        <div>
                            <div class="channel-name">{$snippet['channelTitle']}</div>
                            <div class="text-secondary">{$subscriberCount} Subscribers</div>
                        </div>
                    </div>
                    <div class="actions-container">
                        <a href="{$thumbnailUrl}" download class="action-btn"><i class="fas fa-download"></i> Download Thumbnail</a>
                        <button class="action-btn copy-btn" data-clipboard-text="{$videoUrl}"><i class="fas fa-copy"></i> Copy Video URL</button>
                    </div>
                </header>

                <main class="video-main">
                    <img src="{$thumbnailUrl}" alt="Video Thumbnail" class="video-thumbnail">
                    <div class="video-section stats-grid">
                        <div class="stat-item">
                            <div class="stat-value">{$viewCount}</div>
                            <div class="stat-label">Views</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{$likeCount}</div>
                            <div class="stat-label">Likes</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{$commentCount}</div>
                            <div class="stat-label">Comments</div>
                        </div>
                    </div>
                </main>

                <aside class="video-sidebar">
                    <div class="video-section">
                        <h3 class="section-title">Video Details</h3>
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="stat-label">Published On</div>
                                <div class="stat-value-small">{$publishedDate}</div>
                            </div>
                            <div class="detail-item">
                                <div class="stat-label">Duration</div>
                                <div class="stat-value-small">{$formattedDuration}</div>
                            </div>
                        </div>
                    </div>
                     <div class="video-section">
                        <h3 class="section-title">Estimated Revenue</h3>
                        <div class="stat-item">
                            <div class="stat-value">$ {$estimatedRevenueLow} - $ {$estimatedRevenueHigh}</div>
                        </div>
                        <p class="disclaimer">*Based on a standard RPM range.</p>
                    </div>
                </aside>

                <div class="video-section video-description">
                    <h3 class="section-title">Description</h3>
                    <div class="description-content">{$snippet['description']}</div>
                </div>

                <div class="video-section video-tags">
                    <h3 class="section-title">Tags</h3>
                    <div class="tags-container">
HTML;
            if (!empty($snippet['tags'])) {
                foreach ($snippet['tags'] as $tag) {
                    echo "<span class='tag-item'>{$tag}</span>";
                }
            } else {
                echo "<span>No tags available.</span>";
            }
            echo <<<HTML
                    </div>
                </div>

                <div class="video-section video-extra-stats">
                    <h3 class="section-title">Performance Statistics</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value">{$likesPer1000Views}</div>
                            <div class="stat-label">Likes per 1,000 Views</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{$commentsPer1000Views}</div>
                            <div class="stat-label">Comments per 1,000 Views</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{$likeToCommentRatio}:1</div>
                            <div class="stat-label">Like-to-Comment Ratio</div>
                        </div>
                    </div>
                </div>
            </div>
HTML;

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