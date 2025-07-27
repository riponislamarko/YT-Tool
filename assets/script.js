document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeTheme();
    initializeToolSelector();
    initializeInput();
    initializeSearch();
    initializeKeyboardNavigation();
});

// --- THEME SWITCHER ---
function initializeTheme() {
    const lightBtn = document.getElementById('theme-light-btn');
    const darkBtn = document.getElementById('theme-dark-btn');
    const userPreference = localStorage.getItem('theme');

    const setTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        lightBtn.classList.toggle('active', theme === 'light');
        darkBtn.classList.toggle('active', theme === 'dark');
    };

    // Set initial theme
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialTheme = userPreference || (prefersDark ? 'dark' : 'light');
    setTheme(initialTheme);

    // Add event listeners
    lightBtn.addEventListener('click', () => setTheme('light'));
    darkBtn.addEventListener('click', () => setTheme('dark'));

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if (!localStorage.getItem('theme')) {
            setTheme(e.matches ? 'dark' : 'light');
        }
    });
}

// Enhanced Tool Selector functionality
function initializeToolSelector() {
    const toolButtons = document.querySelectorAll('.tool-btn');
    const searchInput = document.getElementById('youtubeInput');
    
    if (!toolButtons.length) {
        console.error('Tool selector buttons not found');
        return;
    }
    
    // Handle tool button selection
    toolButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const tool = this.getAttribute('data-tool');
            
            // Remove active class from all buttons
            toolButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Store selected tool
            localStorage.setItem('selectedTool', tool);
            
            // Update placeholder based on tool
            updatePlaceholder(tool);
            
            // Focus back to input for better UX
            if (searchInput) {
                searchInput.focus();
            }
        });
        
        // Add keyboard support for tool buttons
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    
    // Restore previously selected tool
    const savedTool = localStorage.getItem('selectedTool');
    if (savedTool) {
        const savedButton = document.querySelector(`[data-tool="${savedTool}"]`);
        if (savedButton) {
            savedButton.click();
        }
    }
}

// Update placeholder based on selected tool
function updatePlaceholder(tool) {
    const input = document.getElementById('youtubeInput');
    if (!input) return;
    
    const placeholders = {
        'data': 'Enter YouTube URL, video ID, channel ID, or channel handle',
        'channel': 'Enter channel URL, channel ID, or channel handle',
        'thumbnail': 'Enter YouTube video URL or video ID',
        'tags': 'Enter YouTube video URL or video ID',
        'search': 'Enter search keywords'
    };
    
    input.placeholder = placeholders[tool] || placeholders['data'];
}

// Initialize input functionality
function initializeInput() {
    const input = document.getElementById('youtubeInput');
    const searchBtn = document.getElementById('searchBtn');
    
    if (!input || !searchBtn) {
        console.error('Input elements not found');
        return;
    }
    
    // Handle Enter key press
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleSearch();
        }
    });
    
    // Handle input changes for real-time validation
    input.addEventListener('input', function() {
        clearStatus();
        
        // Enable/disable search button based on input
        if (this.value.trim()) {
            searchBtn.disabled = false;
        } else {
            searchBtn.disabled = true;
        }
    });
    
    // Handle paste events
    input.addEventListener('paste', function(e) {
        setTimeout(() => {
            if (this.value.trim()) {
                searchBtn.disabled = false;
            }
        }, 10);
    });
    
    // Initialize button state
    searchBtn.disabled = true;
}

// Initialize search functionality
function initializeSearch() {
    const searchBtn = document.getElementById('searchBtn');
    
    if (!searchBtn) {
        console.error('Search button not found');
        return;
    }
    
    searchBtn.addEventListener('click', function(e) {
        e.preventDefault();
        handleSearch();
    });
}

// Initialize keyboard navigation
function initializeKeyboardNavigation() {
    document.addEventListener('keydown', function(e) {
        // Escape key to clear input
        if (e.key === 'Escape') {
            const input = document.getElementById('youtubeInput');
            if (input && document.activeElement === input) {
                input.value = '';
                input.focus();
                clearStatus();
            }
        }
        
        // Ctrl/Cmd + K to focus search input
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const input = document.getElementById('youtubeInput');
            if (input) {
                input.focus();
            }
        }
    });
}

// Main search handler
function handleSearch() {
    const input = document.getElementById('youtubeInput');
    const activeTool = document.querySelector('.tool-btn.active');
    
    if (!input || !activeTool) {
        console.error('Required elements not found');
        return;
    }
    
    const value = input.value.trim();
    const tool = activeTool.getAttribute('data-tool');
    
    if (!value) {
        showStatus('error', 'Please enter a valid input.');
        return;
    }
    
    // Validate input based on tool
    const validation = validateInput(value, tool);
    if (!validation.isValid) {
        showStatus('error', validation.message);
        return;
    }
    
    // Process the request
    processRequest(value, tool);
}

// Validate input based on tool type
function validateInput(value, tool) {
    const urlPattern = /^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/;
    const videoIdPattern = /^[a-zA-Z0-9_-]{11}$/;
    const channelIdPattern = /^UC[a-zA-Z0-9_-]{22}$/;
    const channelHandlePattern = /^@[a-zA-Z0-9_-]+$/;
    
    switch (tool) {
        case 'data':
            if (urlPattern.test(value) || videoIdPattern.test(value) || channelIdPattern.test(value) || channelHandlePattern.test(value)) {
                return { isValid: true };
            }
            return { isValid: false, message: 'Please enter a valid YouTube URL, video ID, channel ID, or channel handle.' };
            
        case 'channel':
            if (urlPattern.test(value) || channelIdPattern.test(value) || channelHandlePattern.test(value)) {
                return { isValid: true };
            }
            return { isValid: false, message: 'Please enter a valid YouTube channel URL, channel ID, or channel handle.' };
            
        case 'thumbnail':
        case 'tags':
            if (urlPattern.test(value) || videoIdPattern.test(value)) {
                return { isValid: true };
            }
            return { isValid: false, message: 'Please enter a valid YouTube video URL or video ID.' };
            
        case 'search':
            if (value.length >= 2) {
                return { isValid: true };
            }
            return { isValid: false, message: 'Please enter at least 2 characters to search.' };
            
        default:
            return { isValid: true };
    }
}

// Process the API request
function processRequest(value, tool) {
    showLoading();
    clearStatus();
    
    // Determine the endpoint based on tool
    let endpoint;
    let params = {};
    
    switch (tool) {
        case 'data':
            endpoint = 'data-viewer.php';
            params = { url: value, tool: 'data' };
            break;
        case 'channel':
            endpoint = 'find-channel.php';
            params = { input: value };
            break;
        case 'thumbnail':
            endpoint = 'get-thumbnail.php';
            params = { video_id: extractVideoId(value) || value };
            break;
        case 'tags':
            endpoint = 'extract-tags.php';
            params = { video_id: extractVideoId(value) || value };
            break;
        case 'search':
            endpoint = 'search-video.php';
            params = { keyword: value };
            break;
        default:
            endpoint = 'index.php';
            params = { url: value };
    }
    
    // Create form data
    const formData = new FormData();
    Object.keys(params).forEach(key => {
        formData.append(key, params[key]);
    });
    
    // Make the request
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(data => {
        hideLoading();
        showResults(data);
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showStatus('error', 'An error occurred while processing your request. Please try again.');
    });
}

// Show loading state
function showLoading() {
    const searchBtn = document.getElementById('searchBtn');
    if (searchBtn) {
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Analyzing...</span>';
        searchBtn.disabled = true;
    }
}

// Hide loading state
function hideLoading() {
    const searchBtn = document.getElementById('searchBtn');
    if (searchBtn) {
        searchBtn.innerHTML = '<span>Analyze</span>';
        searchBtn.disabled = false;
    }
}

// Show results
function showResults(data) {
    const resultsContainer = document.getElementById('resultsContainer');
    const resultsContent = document.getElementById('resultsContent');
    
    if (!resultsContainer || !resultsContent) {
        console.error('Results container not found');
        return;
    }
    
    resultsContent.innerHTML = data;
    resultsContainer.style.display = 'block'; // Make the container visible
    
    // Use a timeout to allow the display property to apply before adding the class for the animation
    setTimeout(() => {
        resultsContainer.classList.add('visible');
    }, 10);

    // Initialize copy buttons in results
    initializeCopyButtons();
    initializeTagCopier();
    
    // Scroll to results
    resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Show status message
function showStatus(type, message) {
    const statusContainer = document.querySelector('.status-container');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    
    if (!statusContainer) return;
    
    // Hide all status messages
    if (successMessage) successMessage.style.display = 'none';
    if (errorMessage) errorMessage.style.display = 'none';
    
    // Show appropriate message
    if (type === 'success' && successMessage) {
        successMessage.querySelector('span').textContent = message;
        successMessage.style.display = 'flex';
    } else if (type === 'error' && errorMessage) {
        errorMessage.querySelector('span').textContent = message;
        errorMessage.style.display = 'flex';
    }
}

// Clear status messages
function clearStatus() {
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    
    if (successMessage) successMessage.style.display = 'none';
    if (errorMessage) errorMessage.style.display = 'none';
}

// Initialize copy buttons
function initializeCopyButtons() {
    const copyButtons = document.querySelectorAll('.copy-btn');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const textToCopy = this.getAttribute('data-clipboard-text') || this.textContent.trim();
            
            if (navigator.clipboard && window.isSecureContext) {
                // Use modern clipboard API
                navigator.clipboard.writeText(textToCopy).then(() => {
                    showCopySuccess(this);
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                    fallbackCopyTextToClipboard(textToCopy, this);
                });
            } else {
                // Fallback for older browsers
                fallbackCopyTextToClipboard(textToCopy, this);
            }
        });
    });
}

// --- TAG COPIER ---
function initializeTagCopier() {
    const copyAllBtn = document.getElementById('copy-all-tags-btn');
    if (!copyAllBtn) return;

    copyAllBtn.addEventListener('click', () => {
        const tagsContainer = document.getElementById('tags-container');
        const tags = Array.from(tagsContainer.querySelectorAll('.tag-item')).map(tag => tag.textContent);
        const tagsString = tags.join(', ');
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(tagsString).then(() => {
                showCopySuccess(copyAllBtn);
            });
        } else {
            fallbackCopyTextToClipboard(tagsString, copyAllBtn);
        }
    });
}

// Show copy success feedback
function showCopySuccess(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Copied!';
    setTimeout(() => {
        button.innerHTML = originalText;
    }, 2000);
}

// Fallback copy function for older browsers
function fallbackCopyTextToClipboard(text, button) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess(button);
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
    }
    
    document.body.removeChild(textArea);
}

// Utility functions
function extractVideoId(url) {
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/v\/([a-zA-Z0-9_-]{11})/,
        /youtube\.com\/watch\?.*v=([a-zA-Z0-9_-]{11})/
    ];
    
    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match) {
            return match[1];
        }
    }
    
    return null;
}