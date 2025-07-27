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
<body class="page-wrapper">
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-play-circle"></i>
                    <span>YouTube Toolkit</span>
                </div>
                <div class="theme-switcher">
                    <button id="theme-light-btn" title="Switch to light mode"><i class="fas fa-sun"></i></button>
                    <button id="theme-dark-btn" title="Switch to dark mode"><i class="fas fa-moon"></i></button>
                </div>
            </div>
        </div>
    </header>

    <main class="site-main">
        <div class="container">
            <section class="hero-section">
                <h1 class="hero-title">The Ultimate YouTube Toolkit</h1>
                <p class="hero-subtitle">All the tools you need to analyze and grow your YouTube channel.</p>
            </section>

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
                            <span>Analyze</span>
                        </button>
                    </div>
                </div>
            </section>
            
            <section class="results-section">
                <div class="results-container" id="resultsContainer">
                    <div class="results-content" id="resultsContent"></div>
                </div>
            </section>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h4 class="footer-heading">About</h4>
                    <p>This YouTube Toolkit is designed to provide valuable insights for creators, marketers, and analysts.</p>
                </div>
                <div class="footer-column">
                    <h4 class="footer-heading">Tools</h4>
                    <ul>
                        <li><a href="#">Data Viewer</a></li>
                        <li><a href="#">Channel Finder</a></li>
                        <li><a href="#">Thumbnail Downloader</a></li>
                        <li><a href="#">Tag Extractor</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4 class="footer-heading">Legal</h4>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4 class="footer-heading">Connect</h4>
                    <div class="social-links">
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="GitHub"><i class="fab fa-github"></i></a>
                        <a href="#" title="Contact"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-copyright">
                <p>&copy; 2024 YouTube Toolkit. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/script.js"></script>
</body>
</html>