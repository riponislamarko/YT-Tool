document.addEventListener('DOMContentLoaded', function() {
    const searchBtn = document.getElementById('searchBtn');
    const youtubeInput = document.getElementById('youtubeInput');
    const resultsContainer = document.getElementById('resultsContainer');
    const resultsContent = document.getElementById('resultsContent');
    const toolButtons = document.querySelectorAll('.tool-btn');
    let selectedTool = 'channel-stats';

    // Tool selection
    toolButtons.forEach(button => {
        button.addEventListener('click', () => {
            toolButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            selectedTool = button.dataset.tool;
        });
    });

    // Search button click
    searchBtn.addEventListener('click', performSearch);
    youtubeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    function extractVideoId(url) {
        const patterns = [
            /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/,
            /^([^"&?\/ ]{11})$/
        ];
        for (let i = 0; i < patterns.length; i++) {
            const match = url.match(patterns[i]);
            if (match) {
                return match[1];
            }
        }
        return null;
    }

    function performSearch() {
        const input = youtubeInput.value.trim();
        if (!input) {
            resultsContent.innerHTML = '<p>Please enter a value.</p>';
            resultsContainer.classList.add('visible');
            return;
        }

        let endpoint = '';
        let body = '';
        switch (selectedTool) {
            case 'channel-stats':
                endpoint = 'analyze-channel.php';
                body = 'channel_input=' + encodeURIComponent(input);
                break;
            case 'video-stats':
                endpoint = 'get-video-stats.php';
                body = 'video_input=' + encodeURIComponent(input);
                break;
            case 'channel':
                endpoint = 'find-channel.php';
                body = 'input=' + encodeURIComponent(input);
                break;
            case 'thumbnail':
                endpoint = 'get-thumbnail.php';
                const thumbVideoId = extractVideoId(input);
                body = 'video_id=' + encodeURIComponent(thumbVideoId);
                break;
            case 'images':
                endpoint = 'get-images.php';
                body = 'channel_input=' + encodeURIComponent(input);
                break;
            case 'tags':
                endpoint = 'extract-tags.php';
                const tagsVideoId = extractVideoId(input);
                body = 'video_id=' + encodeURIComponent(tagsVideoId);
                break;
            case 'search':
                endpoint = 'search-video.php';
                body = 'input=' + encodeURIComponent(input);
                break;
            case 'shadowban':
                endpoint = 'shadowban-detector.php';
                body = 'channel_input=' + encodeURIComponent(input);
                break;
            case 'earnings':
                endpoint = 'earnings-calculator.php';
                body = 'input=' + encodeURIComponent(input);
                break;
            case 'monetization':
                endpoint = 'check-monetization-node.php';
                body = 'channel_input=' + encodeURIComponent(input);
                break;
            case 'data':
                endpoint = 'data-viewer.php';
                body = 'url=' + encodeURIComponent(input) + '&tool=data';
                break;
        }

        // Show loading state
        resultsContent.innerHTML = '<p>Loading...</p>';
        resultsContainer.classList.add('visible');

        // Fetch data
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: body
        })
        .then(response => response.text())
        .then(data => {
            resultsContent.innerHTML = data;
        })
        .catch(error => {
            console.error('Error:', error);
            resultsContent.innerHTML = '<p>An error occurred.</p>';
        });
    }

    // Theme switcher
    const lightBtn = document.getElementById('theme-light-btn');
    const darkBtn = document.getElementById('theme-dark-btn');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    }

    lightBtn.addEventListener('click', () => setTheme('light'));
    darkBtn.addEventListener('click', () => setTheme('dark'));

    // Set initial theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        setTheme(savedTheme);
    } else {
        setTheme(prefersDark.matches ? 'dark' : 'light');
    }
});
