<?php
/**
 * YouTube Utility Tool - Installation Script
 * Run this file once to check system requirements and guide setup
 */

// Check if already installed
if (file_exists('config.php') && !defined('YOUTUBE_API_KEY')) {
    require_once 'config.php';
    if (YOUTUBE_API_KEY !== 'your_youtube_api_key_here') {
        die('Tool is already installed. Delete install.php for security.');
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Utility Tool - Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2); }
        .step { border-left: 4px solid #007bff; padding-left: 1rem; margin-bottom: 1rem; }
        .step.completed { border-left-color: #28a745; }
        .step.error { border-left-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h1 class="mb-0">
                            <i class="fab fa-youtube me-2"></i>
                            YouTube Utility Tool - Installation
                        </h1>
                    </div>
                    <div class="card-body">
                        <h4>System Requirements Check</h4>
                        
                        <?php
                        $requirements_met = true;
                        
                        // Check PHP version
                        echo '<div class="step ' . (version_compare(PHP_VERSION, '7.4.0', '>=') ? 'completed' : 'error') . '">';
                        echo '<h5><i class="fas fa-' . (version_compare(PHP_VERSION, '7.4.0', '>=') ? 'check' : 'times') . ' me-2"></i>PHP Version</h5>';
                        echo '<p>Current: ' . PHP_VERSION . ' (Required: 7.4+)</p>';
                        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
                            $requirements_met = false;
                        }
                        echo '</div>';
                        
                        // Check required PHP extensions
                        $required_extensions = ['json', 'curl', 'openssl'];
                        foreach ($required_extensions as $ext) {
                            $loaded = extension_loaded($ext);
                            echo '<div class="step ' . ($loaded ? 'completed' : 'error') . '">';
                            echo '<h5><i class="fas fa-' . ($loaded ? 'check' : 'times') . ' me-2"></i>' . ucfirst($ext) . ' Extension</h5>';
                            echo '<p>' . ($loaded ? 'Loaded' : 'Not loaded') . '</p>';
                            if (!$loaded) {
                                $requirements_met = false;
                            }
                            echo '</div>';
                        }
                        
                        // Check file permissions
                        $writable_files = ['config.php'];
                        foreach ($writable_files as $file) {
                            $writable = is_writable($file) || !file_exists($file);
                            echo '<div class="step ' . ($writable ? 'completed' : 'error') . '">';
                            echo '<h5><i class="fas fa-' . ($writable ? 'check' : 'times') . ' me-2"></i>File Permissions</h5>';
                            echo '<p>' . $file . ': ' . ($writable ? 'Writable' : 'Not writable') . '</p>';
                            if (!$writable) {
                                $requirements_met = false;
                            }
                            echo '</div>';
                        }
                        
                        // Check if config exists
                        $config_exists = file_exists('config.php');
                        echo '<div class="step ' . ($config_exists ? 'completed' : 'error') . '">';
                        echo '<h5><i class="fas fa-' . ($config_exists ? 'check' : 'times') . ' me-2"></i>Configuration File</h5>';
                        echo '<p>' . ($config_exists ? 'config.php exists' : 'config.php not found') . '</p>';
                        if (!$config_exists) {
                            $requirements_met = false;
                        }
                        echo '</div>';
                        ?>
                        
                        <hr>
                        
                        <?php if ($requirements_met): ?>
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle me-2"></i>All Requirements Met!</h5>
                                <p>Your system meets all requirements for the YouTube Utility Tool.</p>
                            </div>
                            
                            <h4>Next Steps</h4>
                            <div class="step">
                                <h5><i class="fas fa-key me-2"></i>1. Get YouTube API Key</h5>
                                <p>You need a YouTube Data API v3 key to use this tool.</p>
                                <ol>
                                    <li>Go to <a href="https://console.developers.google.com/" target="_blank">Google Cloud Console</a></li>
                                    <li>Create a new project or select existing one</li>
                                    <li>Enable YouTube Data API v3</li>
                                    <li>Create credentials (API Key)</li>
                                    <li>Copy your API key</li>
                                </ol>
                            </div>
                            
                            <div class="step">
                                <h5><i class="fas fa-cog me-2"></i>2. Configure API Key</h5>
                                <p>Edit the <code>config.php</code> file and replace <code>your_youtube_api_key_here</code> with your actual API key.</p>
                                <div class="alert alert-info">
                                    <strong>Example:</strong><br>
                                    <code>define('YOUTUBE_API_KEY', 'AIzaSyC...');</code>
                                </div>
                            </div>
                            
                            <div class="step">
                                <h5><i class="fas fa-rocket me-2"></i>3. Start Using</h5>
                                <p>Once you've added your API key, you can start using the tool!</p>
                                <a href="index.php" class="btn btn-primary">
                                    <i class="fas fa-play me-2"></i>Go to YouTube Tool
                                </a>
                            </div>
                            
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i>Requirements Not Met</h5>
                                <p>Please fix the issues above before proceeding with installation.</p>
                            </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                For detailed installation instructions, see <a href="README.md" target="_blank">README.md</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 