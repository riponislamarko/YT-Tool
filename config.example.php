<?php
/**
 * YouTube Utility Tool - Configuration Example
 * 
 * Copy this file to config.php and add your YouTube Data API v3 key
 * Get your API key from: https://console.developers.google.com/apis/credentials
 */

// YouTube Data API v3 Configuration
define('YOUTUBE_API_KEY', 'your_youtube_api_key_here');

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