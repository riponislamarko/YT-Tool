<?php
/**
 * Channel Analysis Template
 * 
 * This template is used to display the channel analysis results in a clean, modern layout.
 * 
 * @param array $channel_data - Associative array containing channel data
 * @param array $monetization_data - Optional data from the Node.js monetization checker
 */

extract($channel_data);
$monetization_status = $monetization_data['is_monetized'] ?? false;
$monetization_score = $monetization_data['score'] ?? 0;
?>

<div class="channel-analysis-container">
    <!-- Channel Header -->
    <div class="channel-header">
        <div class="channel-banner" style="background-image: url('<?= htmlspecialchars($banner) ?>');">
            <div class="channel-info">
                <div class="channel-avatar" style="background-image: url('<?= htmlspecialchars($thumbnail) ?>');"></div>
                <div class="channel-meta">
                    <h1 class="channel-title"><?= htmlspecialchars($title) ?></h1>
                    <div class="channel-stats">
                        <span class="stat"><?= number_format($subscriberCount) ?> subscribers</span>
                        <span class="stat"><?= number_format($videoCount) ?> videos</span>
                        <span class="stat"><?= number_format($viewCount) ?> views</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monetization Status Card -->
    <div class="status-card <?= $monetization_status ? 'monetized' : 'not-monetized' ?>">
        <div class="status-icon">
            <?php if ($monetization_status): ?>
                <i class="fas fa-check-circle"></i>
            <?php else: ?>
                <i class="fas fa-times-circle"></i>
            <?php endif; ?>
        </div>
        <div class="status-content">
            <h3>Monetization Status: <span><?= $monetization_status ? 'MONETIZED' : 'NOT MONETIZED' ?></span></h3>
            <div class="confidence-meter">
                <div class="confidence-bar" style="width: <?= $monetization_score ?>%;"></div>
                <span class="confidence-text">Confidence: <?= $monetization_score ?>%</span>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="analysis-grid">
        <!-- Channel Info Column -->
        <div class="info-card">
            <h3><i class="fas fa-info-circle"></i> Channel Info</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Channel ID:</span>
                    <span class="value"><?= htmlspecialchars($channel_id) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Created:</span>
                    <span class="value"><?= date('M j, Y', strtotime($publishedAt)) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Country:</span>
                    <span class="value"><?= htmlspecialchars($country ?? 'Not specified') ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Category:</span>
                    <span class="value"><?= htmlspecialchars($category ?? 'Not specified') ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Keywords:</span>
                    <span class="value"><?= htmlspecialchars($keywords ?? 'None') ?></span>
                </div>
            </div>
        </div>

        <!-- Monetization Details Column -->
        <div class="info-card">
            <h3><i class="fas fa-chart-line"></i> Monetization Details</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Ads Status:</span>
                    <span class="value"><?= $monetization_status ? 'Active' : 'Inactive' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Authenticity:</span>
                    <span class="value">Verified (<?= $monetization_score ?>%)</span>
                </div>
                <div class="info-item">
                    <span class="label">Restrictions:</span>
                    <span class="value">None detected</span>
                </div>
                <div class="info-item">
                    <span class="label">Last Checked:</span>
                    <span class="value"><?= date('M j, Y H:i:s') ?></span>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="stats-card">
            <h3><i class="fas fa-chart-bar"></i> Channel Statistics</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?= number_format($subscriberCount) ?></div>
                    <div class="stat-label">Subscribers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= number_format($viewCount) ?></div>
                    <div class="stat-label">Total Views</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= number_format($videoCount) ?></div>
                    <div class="stat-label">Videos</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= $avgViews ? number_format($avgViews) : 'N/A' ?></div>
                    <div class="stat-label">Avg. Views</div>
                </div>
            </div>
        </div>

        <!-- Additional Information Card -->
        <div class="info-card">
            <h3><i class="fas fa-info-circle"></i> Additional Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Custom URL:</span>
                    <span class="value"><?= $customUrl ? 'youtube.com/' . htmlspecialchars($customUrl) : 'Not set' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Default Language:</span>
                    <span class="value"><?= htmlspecialchars($defaultLanguage ?? 'Not specified') ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Privacy Status:</span>
                    <span class="value"><?= ucfirst($privacyStatus ?? 'public') ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Made for Kids:</span>
                    <span class="value"><?= $madeForKids ? 'Yes' : 'No' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Description Section -->
    <?php if (!empty($description)): ?>
    <div class="description-card">
        <h3><i class="fas fa-align-left"></i> Channel Description</h3>
        <div class="description-content">
            <?= nl2br(htmlspecialchars($description)) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Related Actions -->
    <div class="actions-card">
        <h3>Related Tools</h3>
        <div class="action-buttons">
            <a href="#" class="btn btn-primary">
                <i class="fas fa-calculator"></i> Earnings Calculator
            </a>
            <a href="#" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Tag Extractor
            </a>
            <a href="#" class="btn btn-secondary">
                <i class="fas fa-image"></i> Thumbnail Downloader
            </a>
        </div>
    </div>
</div>

<!-- CSS Styles -->
<style>
/* Base Styles */
.channel-analysis-container {
    font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    color: #333;
    line-height: 1.6;
}

/* Header Styles */
.channel-header {
    margin-bottom: 24px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.channel-banner {
    height: 200px;
    background-size: cover;
    background-position: center;
    position: relative;
    background-color: #f5f5f5;
}

.channel-info {
    display: flex;
    align-items: flex-end;
    padding: 20px;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
    height: 100%;
    position: relative;
    z-index: 1;
}

.channel-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 3px solid #fff;
    background-size: cover;
    background-position: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    margin-right: 20px;
    margin-bottom: -20px;
    background-color: #f0f0f0;
}

.channel-meta {
    color: #fff;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
}

.channel-title {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 600;
}

.channel-stats {
    display: flex;
    gap: 15px;
    font-size: 14px;
    opacity: 0.9;
}

/* Status Card */
.status-card {
    display: flex;
    align-items: center;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.status-card.monetized {
    background-color: #e8f5e9;
    border-left: 4px solid #4caf50;
}

.status-card.not-monetized {
    background-color: #ffebee;
    border-left: 4px solid #f44336;
}

.status-icon {
    font-size: 36px;
    margin-right: 20px;
}

.status-card.monetized .status-icon {
    color: #4caf50;
}

.status-card.not-monetized .status-icon {
    color: #f44336;
}

.status-content h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
}

.status-content h3 span {
    font-weight: 600;
}

.confidence-meter {
    background: #e0e0e0;
    height: 6px;
    border-radius: 3px;
    margin-top: 8px;
    overflow: hidden;
}

.confidence-bar {
    height: 100%;
    background: #4caf50;
    width: 0;
    transition: width 0.5s ease;
}

.confidence-text {
    display: block;
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

/* Grid Layout */
.analysis-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

/* Card Styles */
.info-card, .stats-card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border: 1px solid #eee;
}

.info-card h3, .stats-card h3 {
    margin-top: 0;
    margin-bottom: 16px;
    font-size: 16px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-card h3 i, .stats-card h3 i {
    color: #666;
}

/* Info Grid */
.info-grid {
    display: grid;
    gap: 12px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.label {
    color: #666;
    font-weight: 500;
}

.value {
    text-align: right;
    color: #333;
    font-weight: 400;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.stat-item {
    text-align: center;
    padding: 12px;
    background: #f9f9f9;
    border-radius: 6px;
}

.stat-value {
    font-size: 20px;
    font-weight: 600;
    color: #2196f3;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Description Card */
.description-card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border: 1px solid #eee;
}

.description-content {
    line-height: 1.6;
    color: #444;
    white-space: pre-line;
}

/* Actions Card */
.actions-card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border: 1px solid #eee;
}

.actions-card h3 {
    margin-top: 0;
    margin-bottom: 16px;
    font-size: 16px;
    color: #333;
}

.action-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn i {
    font-size: 14px;
}

.btn-primary {
    background-color: #2196f3;
    color: white;
    border: 1px solid #2196f3;
}

.btn-primary:hover {
    background-color: #1976d2;
    border-color: #1976d2;
}

.btn-secondary {
    background-color: #fff;
    color: #2196f3;
    border: 1px solid #e0e0e0;
}

.btn-secondary:hover {
    background-color: #f5f5f5;
    border-color: #bdbdbd;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .analysis-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .channel-info {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .channel-avatar {
        margin-bottom: 10px;
        margin-right: 0;
    }
}

/* Loading Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.channel-analysis-container > * {
    animation: fadeIn 0.3s ease-out forwards;
    opacity: 0;
}

.channel-analysis-container > *:nth-child(1) { animation-delay: 0.1s; }
.channel-analysis-container > *:nth-child(2) { animation-delay: 0.2s; }
.channel-analysis-container > *:nth-child(3) { animation-delay: 0.3s; }
</style>
