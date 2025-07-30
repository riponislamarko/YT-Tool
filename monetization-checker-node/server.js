const express = require('express');
const puppeteer = require('puppeteer');

const app = express();
const port = 3000;

// Serve static files from the 'public' directory
app.use(express.static('public'));
app.use(express.json());

// Endpoint to check monetization
app.post('/check', async (req, res) => {
    const { channelUrl } = req.body;
    const apiKey = process.env.YOUTUBE_API_KEY; // Make sure to set this environment variable

    if (!apiKey) {
        return res.status(500).json({ error: 'YouTube API key is not configured on the server.' });
    }

    if (!channelUrl) {
        return res.status(400).json({ error: 'Channel URL is required.' });
    }

    try {
        // --- Placeholder for the full logic ---
        // In the next steps, we will implement:
        // 1. Channel ID extraction from URL
        // 2. Puppeteer scraping for 'Join' button and ads
        // 3. YouTube API call for channel stats
        // 4. Scoring logic

        // For now, return a mock response
        const mockResponse = {
            status: '✅ Highly Likely Monetized',
            score: 85,
            details: {
                joinButton: { found: true, points: 50 },
                midRollAds: { found: true, points: 30 },
                subscribers: { count: 150000, meetsThreshold: true, points: 10 },
                videoCount: { count: 250, meetsThreshold: true, points: 5 },
                channelAge: { days: 730, meetsThreshold: true, points: 5 },
            }
        };

        res.json(mockResponse);

    } catch (error) {
        console.error('Error during check:', error);
        res.status(500).json({ error: 'An error occurred while checking the channel.' });
    }
});

app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
});
