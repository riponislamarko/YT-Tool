// YouTube Utility Tool - JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all forms with AJAX submission
    initializeForms();
    
    // Initialize copy buttons
    initializeCopyButtons();
});

function initializeForms() {
    const forms = [
        { id: 'channelForm', resultId: 'channelResult' },
        { id: 'thumbnailForm', resultId: 'thumbnailResult' },
        { id: 'tagsForm', resultId: 'tagsResult' },
        { id: 'searchForm', resultId: 'searchResult' }
    ];
    
    forms.forEach(form => {
        const formElement = document.getElementById(form.id);
        if (formElement) {
            formElement.addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm(formElement, form.resultId);
            });
        }
    });
}

function submitForm(form, resultId) {
    const resultDiv = document.getElementById(resultId);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<span class="loading me-2"></span>Processing...';
    submitBtn.disabled = true;
    
    // Clear previous results
    resultDiv.innerHTML = '';
    
    // Get form data
    const formData = new FormData(form);
    
    // Make AJAX request
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        resultDiv.innerHTML = data;
        initializeCopyButtons(); // Re-initialize copy buttons for new content
    })
    .catch(error => {
        resultDiv.innerHTML = `
            <div class="result-container result-error">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Error</h5>
                <p>An error occurred while processing your request. Please try again.</p>
                <small class="text-muted">Error: ${error.message}</small>
            </div>
        `;
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function initializeCopyButtons() {
    const copyButtons = document.querySelectorAll('.copy-btn');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const textToCopy = this.getAttribute('data-clipboard-text');
            
            if (navigator.clipboard && window.isSecureContext) {
                // Use modern clipboard API
                navigator.clipboard.writeText(textToCopy).then(() => {
                    showCopySuccess(this);
                }).catch(err => {
                    fallbackCopyTextToClipboard(textToCopy, this);
                });
            } else {
                // Fallback for older browsers
                fallbackCopyTextToClipboard(textToCopy, this);
            }
        });
    });
}

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
        } else {
            showCopyError(button);
        }
    } catch (err) {
        showCopyError(button);
    }
    
    document.body.removeChild(textArea);
}

function showCopySuccess(button) {
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    button.classList.add('copied');
    
    setTimeout(() => {
        button.textContent = originalText;
        button.classList.remove('copied');
    }, 2000);
}

function showCopyError(button) {
    const originalText = button.textContent;
    button.textContent = 'Failed';
    button.style.background = '#dc3545';
    
    setTimeout(() => {
        button.textContent = originalText;
        button.style.background = '';
    }, 2000);
}

// Utility function to validate YouTube video ID
function isValidVideoId(videoId) {
    return /^[a-zA-Z0-9_-]{11}$/.test(videoId);
}

// Utility function to extract video ID from URL
function extractVideoId(url) {
    const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
    const match = url.match(regex);
    return match ? match[1] : null;
}

// Auto-extract video ID from URL input
document.addEventListener('input', function(e) {
    if (e.target.id === 'videoId' || e.target.id === 'videoIdTags') {
        const value = e.target.value.trim();
        
        // If it looks like a full URL, try to extract video ID
        if (value.includes('youtube.com') || value.includes('youtu.be')) {
            const videoId = extractVideoId(value);
            if (videoId) {
                e.target.value = videoId;
            }
        }
    }
});

// Add tooltips for better UX
document.addEventListener('DOMContentLoaded', function() {
    // Add tooltips to form inputs
    const inputs = document.querySelectorAll('input[placeholder]');
    inputs.forEach(input => {
        input.title = input.placeholder;
    });
    
    // Add tooltips to buttons
    const buttons = document.querySelectorAll('button[type="submit"]');
    buttons.forEach(button => {
        const icon = button.querySelector('i');
        if (icon) {
            const tooltipText = button.textContent.trim();
            button.title = tooltipText;
        }
    });
});

// Smooth scrolling for better UX
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Auto-resize textareas (if any are added in the future)
document.addEventListener('input', function(e) {
    if (e.target.tagName === 'TEXTAREA') {
        e.target.style.height = 'auto';
        e.target.style.height = e.target.scrollHeight + 'px';
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + Enter to submit form
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        const activeElement = document.activeElement;
        if (activeElement && activeElement.closest('form')) {
            const form = activeElement.closest('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.click();
            }
        }
    }
    
    // Tab navigation with arrow keys
    if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
        const activeTab = document.querySelector('.nav-link.active');
        if (activeTab) {
            const tabList = Array.from(document.querySelectorAll('.nav-link'));
            const currentIndex = tabList.indexOf(activeTab);
            let newIndex;
            
            if (e.key === 'ArrowLeft') {
                newIndex = currentIndex > 0 ? currentIndex - 1 : tabList.length - 1;
            } else {
                newIndex = currentIndex < tabList.length - 1 ? currentIndex + 1 : 0;
            }
            
            tabList[newIndex].click();
        }
    }
}); 