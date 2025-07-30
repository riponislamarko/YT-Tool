require('dotenv').config();
const express = require('express');
const puppeteer = require('puppeteer');

const app = express();
const port = 3000;

// Serve static files from the 'public' directory
app.use(express.static('public'));
app.use(express.json());

// --- Helper Functions ---

/**
 * Extracts a potential ID or handle from various YouTube URL formats or direct input.
 * @param {string} input The YouTube URL or handle.
 * @returns {string|null} The extracted part (channel ID, custom URL name, or handle) or null.
 */
function extractIdFromInput(input) {
    const patterns = [
        /youtube\.com\/channel\/([a-zA-Z0-9_-]{24})/,      // Standard Channel ID
        /youtube\.com\/c\/([a-zA-Z0-9_.-]+)/,             // Custom URL (legacy)
        /youtube\.com\/@([a-zA-Z0-9_.-]+)/,              // Handle URL
        /youtube\.com\/user\/([a-zA-Z0-9_.-]+)/           // User URL (legacy)
    ];
    for (const pattern of patterns) {
        const match = input.match(pattern);
        if (match && match[1]) return match[1];
    }
    if (input.startsWith('@')) return input.substring(1); // Direct handle
    if (/^[a-zA-Z0-9_.-]+$/.test(input)) return input; // Assume it's a direct ID/handle
    return null;
}

/**
 * Gets channel statistics and snippet from the YouTube Data API.
 * @param {string} channelId The YouTube channel ID.
 * @param {string} apiKey The YouTube API key.
 * @returns {Promise<object|null>} The channel data or null.
 */
async function getChannelData(channelId, apiKey) {
    const url = `https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id=${channelId}&key=${apiKey}`;
    const fetch = (await import('node-fetch')).default;
    const response = await fetch(url);
    const data = await response.json();
    if (data.items && data.items.length > 0) return data.items[0];
    return null;
}

/**
 * Resolves a handle or custom URL to a channel ID using the API.
 * @param {string} handle The handle or custom URL name.
 * @param {string} apiKey The YouTube API key.
 * @returns {Promise<string|null>} The channel ID or null.
 */
async function resolveHandleToId(handle, apiKey) {
    const url = `https://www.googleapis.com/youtube/v3/search?part=snippet&q=${encodeURIComponent(handle)}&type=channel&key=${apiKey}`;
    const fetch = (await import('node-fetch')).default;
    const response = await fetch(url);
    const data = await response.json();
    // Find the most likely match
    if (data.items && data.items.length > 0) {
        const perfectMatch = data.items.find(item => item.snippet.customUrl === `@${handle}` || item.snippet.title.toLowerCase() === handle.toLowerCase());
        return perfectMatch ? perfectMatch.snippet.channelId : data.items[0].snippet.channelId;
    }
    return null;
}

/**
 * Uses Puppeteer to check for a "Join" button on the channel's homepage.
 * @param {string} channelId The YouTube channel ID.
 * @returns {Promise<boolean>} True if the button is found.
 */
async function checkForJoinButton(channelId) {
    let browser;
    try {
        browser = await puppeteer.launch({ headless: true, args: ['--no-sandbox'] });
        const page = await browser.newPage();
        await page.goto(`https://www.youtube.com/channel/${channelId}`, { waitUntil: 'networkidle2' });
        const joinButton = await page.evaluate(() => {
            const buttons = Array.from(document.querySelectorAll('#sponsor-button a, #sponsor-button button, a.ytd-c4-tabbed-header-renderer'));
            return buttons.some(btn => btn.textContent.trim().toLowerCase() === 'join');
        });
        return joinButton;
    } catch (error) {
        console.error('Puppeteer error checking for Join button:', error);
        return false;
    } finally {
        if (browser) await browser.close();
    }
}

// Main endpoint to check monetization
app.post('/check', async (req, res) => {
    const { channelUrl } = req.body;
    const apiKey = process.env.YOUTUBE_API_KEY;

    if (!apiKey) return res.status(500).json({ error: 'YouTube API key is not configured.' });
    if (!channelUrl) return res.status(400).json({ error: 'Channel URL is required.' });

    try {
        let score = 0;
        const details = {};

        // 1. Get Channel ID
        let extractedId = extractIdFromInput(channelUrl);
        if (!extractedId) return res.status(400).json({ error: 'Could not parse channel URL or handle.' });

        let channelId = extractedId.startsWith('UC') ? extractedId : await resolveHandleToId(extractedId, apiKey);
        if (!channelId) return res.status(404).json({ error: `Could not find a channel for "${extractedId}".` });

        // 2. Perform Checks
        const [joinButtonFound, channelData] = await Promise.all([
            checkForJoinButton(channelId),
            getChannelData(channelId, apiKey)
        ]);

        if (!channelData) return res.status(404).json({ error: 'Could not fetch channel data from YouTube API.' });

        // 3. Calculate Score
        details.joinButton = { found: joinButtonFound, points: joinButtonFound ? 50 : 0 };
        if (joinButtonFound) score += 50;
        
        // NOTE: Mid-roll ad detection is complex and less reliable without video-specific checks.
        // Per the prompt, we are focusing on the Join button and API heuristics for this implementation.
        details.midRollAds = { found: false, points: 0 }; // Placeholder

        const stats = channelData.statistics;
        const snippet = channelData.snippet;
        
        const hasEnoughSubs = parseInt(stats.subscriberCount, 10) >= 1000;
        details.subscribers = { found: hasEnoughSubs, points: hasEnoughSubs ? 10 : 0 };
        if (hasEnoughSubs) score += 10;

        const hasEnoughVideos = parseInt(stats.videoCount, 10) >= 5;
        details.videoCount = { found: hasEnoughVideos, points: hasEnoughVideos ? 5 : 0 };
        if (hasEnoughVideos) score += 5;

        const channelAgeDays = (new Date() - new Date(snippet.publishedAt)) / (1000 * 60 * 60 * 24);
        const isOldEnough = channelAgeDays >= 90;
        details.channelAge = { found: isOldEnough, points: isOldEnough ? 5 : 0 };
        if (isOldEnough) score += 5;

        // 4. Determine Final Status
        let status;
        if (score >= 70) status = '✅ Highly Likely Monetized';
        else if (score >= 50) status = '⚠️ Possibly Monetized';
        else status = '❌ Not Monetized';

        res.json({ status, score, details });

    } catch (error) {
        console.error('Error during check:', error);
        res.status(500).json({ error: 'An error occurred while checking the channel.' });
    }
});

app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
});
