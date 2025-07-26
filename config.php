<?php
/**
 * YouTube Utility Tool - Configuration
 * 
 * Add your YouTube Data API v3 key here
 * Get your API key from: https://console.developers.google.com/apis/credentials
 */

// YouTube Data API v3 Configuration
define('YOUTUBE_API_KEY', 'AIzaSyDhMFc83yL-kNFkmVTyZ11OZxwRjaIE8W0');

// Optional: Rate limiting settings
define('MAX_REQUESTS_PER_MINUTE', 100);
define('REQUEST_TIMEOUT', 30);

// Optional: Cache settings
define('ENABLE_CACHE', true);
define('CACHE_DURATION', 3600);

// Error reporting (set to false in production)
define('DEBUG_MODE', true);

// Security settings
define('ALLOWED_ORIGINS', ['*']); // Restrict in production
define('MAX_INPUT_LENGTH', 1000);
?> 