# YouTube Utility Tool

A comprehensive PHP-based YouTube data extraction and analysis tool with a modern, responsive interface. This tool provides four main features for working with YouTube data using the YouTube Data API v3.

## 🚀 Features

### 1. **Channel ID Finder**
- Find YouTube channel IDs from handles, usernames, or custom URLs
- Supports various input formats: `@username`, `username`, or full YouTube URLs
- Returns channel ID with copy functionality

### 2. **Thumbnail Downloader**
- Extract thumbnail download links for any YouTube video
- Multiple quality options: Default, Medium, High, Standard, Max Resolution
- Preview thumbnails with direct download links

### 3. **Tag Extractor**
- Extract all video tags from YouTube videos
- Copy individual tags or all tags at once
- Clean, organized tag display

### 4. **Video Search**
- Search YouTube videos by keyword
- Returns top 5 results with thumbnails and metadata
- Copy video IDs and URLs with one click

## 📁 File Structure

```
yt-tool/
├── index.php                # Main interface with tabbed UI
├── find-channel.php         # Channel ID finder backend
├── get-thumbnail.php        # Thumbnail downloader backend
├── extract-tags.php         # Tag extractor backend
├── search-video.php         # Video search backend
├── config.php              # Configuration file (API key)
├── config.example.php      # Example configuration
├── README.md               # This file
├── .htaccess              # URL rewriting (optional)
└── assets/
    ├── style.css           # Custom styling
    └── script.js           # JavaScript functionality
```

## 🛠️ Installation

### Prerequisites
- PHP 7.4 or higher
- Web server (Apache/Nginx) or local development environment
- YouTube Data API v3 key

### Step 1: Get YouTube API Key
1. Go to [Google Cloud Console](https://console.developers.google.com/)
2. Create a new project or select existing one
3. Enable YouTube Data API v3
4. Create credentials (API Key)
5. Copy your API key

### Step 2: Configure the Tool
1. Copy `config.example.php` to `config.php`
2. Edit `config.php` and replace `your_youtube_api_key_here` with your actual API key:

```php
define('YOUTUBE_API_KEY', 'YOUR_ACTUAL_API_KEY_HERE');
```

### Step 3: Upload to Server
**For cPanel:**
1. Create a folder in `public_html` (e.g., `yt-tool`)
2. Upload all files to this folder
3. Access via: `https://yourdomain.com/yt-tool/`

**For Local Development:**
1. Place files in your web server directory
2. Access via: `http://localhost/yt-tool/`

## 🎯 Usage

### Channel ID Finder
1. Go to the "Channel ID Finder" tab
2. Enter a YouTube handle (e.g., `@pewdiepie`) or full URL
3. Click "Find Channel ID"
4. Copy the channel ID or visit the channel URL

### Thumbnail Downloader
1. Go to the "Thumbnail Downloader" tab
2. Enter a YouTube video ID (11 characters)
3. Click "Get Thumbnails"
4. Download thumbnails in different qualities

### Tag Extractor
1. Go to the "Tag Extractor" tab
2. Enter a YouTube video ID
3. Click "Extract Tags"
4. Copy individual tags or all tags at once

### Video Search
1. Go to the "Video Search" tab
2. Enter search keywords
3. Click "Search Videos"
4. Browse results and copy video IDs/URLs

## 🔧 Configuration Options

Edit `config.php` to customize:

```php
// Rate limiting
define('MAX_REQUESTS_PER_MINUTE', 100);
define('REQUEST_TIMEOUT', 30);

// Cache settings
define('ENABLE_CACHE', true);
define('CACHE_DURATION', 3600);

// Debug mode (set to false in production)
define('DEBUG_MODE', true);

// Security settings
define('MAX_INPUT_LENGTH', 1000);
```

## 🎨 Customization

### Styling
- Edit `assets/style.css` to customize colors, fonts, and layout
- The tool uses Bootstrap 5 for responsive design
- Font Awesome icons are included for better UX

### Functionality
- Modify `assets/script.js` to add custom JavaScript features
- Edit individual PHP files to customize API responses
- Add new tabs by extending the interface

## 🔒 Security Features

- Input sanitization and validation
- API key protection
- Rate limiting support
- Error handling with debug mode toggle
- XSS protection through proper escaping

## 📱 Responsive Design

The tool is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- All modern browsers

## 🚨 Troubleshooting

### Common Issues

**"Configuration Error"**
- Make sure you've added your YouTube API key to `config.php`
- Verify the API key is valid and has YouTube Data API v3 enabled

**"API Error"**
- Check your internet connection
- Verify API quota limits
- Ensure the video/channel is publicly accessible

**"Video Not Found"**
- Double-check the video ID (should be 11 characters)
- Make sure the video is not private or deleted

### Debug Mode
Enable debug mode in `config.php` to see detailed error messages:
```php
define('DEBUG_MODE', true);
```

## 📄 License

This project is open source and available under the MIT License.

## 🤝 Contributing

Feel free to contribute by:
- Reporting bugs
- Suggesting new features
- Submitting pull requests
- Improving documentation

## 📞 Support

For support or questions:
1. Check the troubleshooting section
2. Review the YouTube Data API documentation
3. Ensure your API key has proper permissions

## 🔄 Updates

To update the tool:
1. Backup your `config.php` file
2. Download the latest version
3. Replace files (except `config.php`)
4. Test functionality

---

**Note:** This tool is for educational and personal use. Please respect YouTube's Terms of Service and API usage limits. 