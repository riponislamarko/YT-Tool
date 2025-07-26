<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Utility Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h1 class="mb-0">
                            <i class="fab fa-youtube me-2"></i>
                            YouTube Utility Tool
                        </h1>
                        <p class="mb-0 mt-2">Professional YouTube Data Extraction & Analysis</p>
                    </div>
                    
                    <div class="card-body">
                        <!-- Navigation Tabs -->
                        <ul class="nav nav-tabs" id="toolTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="channel-tab" data-bs-toggle="tab" data-bs-target="#channel" type="button" role="tab">
                                    <i class="fas fa-search me-2"></i>Channel ID Finder
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="thumbnail-tab" data-bs-toggle="tab" data-bs-target="#thumbnail" type="button" role="tab">
                                    <i class="fas fa-image me-2"></i>Thumbnail Downloader
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tags-tab" data-bs-toggle="tab" data-bs-target="#tags" type="button" role="tab">
                                    <i class="fas fa-tags me-2"></i>Tag Extractor
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="search-tab" data-bs-toggle="tab" data-bs-target="#search" type="button" role="tab">
                                    <i class="fas fa-video me-2"></i>Video Search
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content mt-4" id="toolTabsContent">
                            <!-- Channel ID Finder Tab -->
                            <div class="tab-pane fade show active" id="channel" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4><i class="fas fa-search me-2"></i>Find Channel ID</h4>
                                        <p class="text-muted">Enter a YouTube handle or custom URL to find the channel ID</p>
                                        
                                        <form action="find-channel.php" method="POST" id="channelForm">
                                            <div class="mb-3">
                                                <label for="channelInput" class="form-label">YouTube Handle or URL:</label>
                                                <input type="text" class="form-control" id="channelInput" name="channel_input" 
                                                       placeholder="e.g., @pewdiepie or https://youtube.com/@pewdiepie" required>
                                                <div class="form-text">Enter username, handle, or full YouTube URL</div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search me-2"></i>Find Channel ID
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="channelResult"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Thumbnail Downloader Tab -->
                            <div class="tab-pane fade" id="thumbnail" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4><i class="fas fa-image me-2"></i>Download Thumbnails</h4>
                                        <p class="text-muted">Enter a YouTube video ID to get thumbnail download links</p>
                                        
                                        <form action="get-thumbnail.php" method="POST" id="thumbnailForm">
                                            <div class="mb-3">
                                                <label for="videoId" class="form-label">Video ID:</label>
                                                <input type="text" class="form-control" id="videoId" name="video_id" 
                                                       placeholder="e.g., dQw4w9WgXcQ" required>
                                                <div class="form-text">Enter the 11-character video ID from YouTube URL</div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-download me-2"></i>Get Thumbnails
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="thumbnailResult"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tag Extractor Tab -->
                            <div class="tab-pane fade" id="tags" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4><i class="fas fa-tags me-2"></i>Extract Video Tags</h4>
                                        <p class="text-muted">Enter a YouTube video ID to extract all video tags</p>
                                        
                                        <form action="extract-tags.php" method="POST" id="tagsForm">
                                            <div class="mb-3">
                                                <label for="videoIdTags" class="form-label">Video ID:</label>
                                                <input type="text" class="form-control" id="videoIdTags" name="video_id" 
                                                       placeholder="e.g., dQw4w9WgXcQ" required>
                                                <div class="form-text">Enter the 11-character video ID from YouTube URL</div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-tags me-2"></i>Extract Tags
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="tagsResult"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Video Search Tab -->
                            <div class="tab-pane fade" id="search" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4><i class="fas fa-video me-2"></i>Search Videos</h4>
                                        <p class="text-muted">Search for YouTube videos by keyword</p>
                                        
                                        <form action="search-video.php" method="POST" id="searchForm">
                                            <div class="mb-3">
                                                <label for="searchKeyword" class="form-label">Search Keyword:</label>
                                                <input type="text" class="form-control" id="searchKeyword" name="keyword" 
                                                       placeholder="e.g., programming tutorials" required>
                                                <div class="form-text">Enter keywords to search for videos</div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search me-2"></i>Search Videos
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <div id="searchResult"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/script.js"></script>
</body>
</html> 