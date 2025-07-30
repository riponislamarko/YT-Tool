<?php
/**
 * All-in-One YouTube Channel Analyzer
 *
 * Fetches comprehensive data for a given YouTube channel.
 */

require_once 'config.php';

// --- Helper Functions ---

function get_channel_id_from_input($input, $api_key) {
    if (preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $input)) {
        return $input;
    }
    if (preg_match('/youtube\.com\/(?:channel\/|c\/|user\/|@)([^\/?]+)/', $input, $matches)) {
        $query = $matches[1];
        if (preg_match('/^UC[a-zA-Z0-9_-]{22}$/', $query)) {
            return $query;
        }
    } else {
        $query = $input;
    }

    $search_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . urlencode($query) . "&type=channel&maxResults=1&key=" . $api_key;
    $response = @file_get_contents($search_url);
    if ($response === false) return null;

    $data = json_decode($response, true);
    if (empty($data['items'])) return null;

    return $data['items'][0]['snippet']['channelId'];
}

function format_number($num) {
    return number_format($num);
}

// --- Main Logic ---

if (!isset($_POST['channel_input']) || empty($_POST['channel_input'])) {
    echo '<div class="result-card error"><p>Please provide a channel URL or ID.</p></div>';
    exit;
}

$input = trim($_POST['channel_input']);
$api_key = YOUTUBE_API_KEY;

$channel_id = get_channel_id_from_input($input, $api_key);

if (!$channel_id) {
    echo '<div class="result-card error"><p>Could not find a channel with the provided input.</p></div>';
    exit;
}

// --- Fetch All Channel Data ---
$channel_url = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics,brandingSettings&id={$channel_id}&key={$api_key}";
$channel_response = @file_get_contents($channel_url);
if (!$channel_response || empty(json_decode($channel_response, true)['items'])) {
    echo '<div class="result-card error"><p>Could not retrieve channel data.</p></div>';
    exit;
}
$channel_data = json_decode($channel_response, true)['items'][0];

// --- Fetch Recent Videos ---
$videos_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&channelId={$channel_id}&order=date&type=video&maxResults=5&key={$api_key}";
$videos_response = @file_get_contents($videos_url);
$recent_videos = $videos_response ? json_decode($videos_response, true)['items'] : [];

// --- Monetization & Advanced Checks ---
$monetization_reasons = [];

// 1. Advanced check via ytInitialPlayerResponse scraping
if (!empty($recent_videos)) {
    $latest_video_id = $recent_videos[0]['id']['videoId'];
    $watch_page_content = @file_get_contents("https://www.youtube.com/watch?v={$latest_video_id}");
    if ($watch_page_content) {
        // Extract the ytInitialPlayerResponse JSON from the HTML
        if (preg_match('/var ytInitialPlayerResponse = ({.*?});/', $watch_page_content, $matches)) {
            $player_response = json_decode($matches[1], true);
            // Check for the isMonetizable flag
            if (isset($player_response['playabilityStatus']['isMonetizable']) && $player_response['playabilityStatus']['isMonetizable'] === true) {
                $monetization_reasons[] = 'Video is monetizable (via page data)';
            }
        }
    }
}

// 2. Check for a "Join" button on the channel page
$channel_page_url = "https://www.youtube.com/channel/{$channel_id}";
$channel_page_content = @file_get_contents($channel_page_url);
if ($channel_page_content && strpos($channel_page_content, '"text":"Join"') !== false) {
    $monetization_reasons[] = 'Channel Memberships (Join button) found';
}

// 3. Subscriber count heuristic
if ($channel_data['statistics']['subscriberCount'] >= 1000) {
    $monetization_reasons[] = 'Meets subscriber requirement (&gt;1,000)';
}

$is_monetized = !empty($monetization_reasons);

// --- Prepare Data for Display ---
$snippet = $channel_data['snippet'];
$stats = $channel_data['statistics'];
$branding = $channel_data['brandingSettings']['image'] ?? [];

$title = htmlspecialchars($snippet['title']);
$description = htmlspecialchars($snippet['description']);
$profile_pic = str_replace('=s88-c-k-c0x00ffffff-no-rj', '=s800-c-k-c0x00ffffff-no-rj', $snippet['thumbnails']['high']['url']);
$banner = isset($branding['bannerExternalUrl']) ? $branding['bannerExternalUrl'] . '=w2120-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj' : null;

$subscribers = format_number($stats['subscriberCount']);
$total_views = format_number($stats['viewCount']);
$total_videos = format_number($stats['videoCount']);

$earnings_low = number_format(($stats['viewCount'] / 1000) * 1.5, 2);
$earnings_high = number_format(($stats['viewCount'] / 1000) * 4.0, 2);

// --- Generate HTML Output ---
ob_start();
?>
<div class="channel-analyzer-result">
    <div class="channel-header">
        <?php if ($banner): ?>
            <img src="<?= $banner ?>" alt="Channel Banner" class="channel-banner">
        <?php endif; ?>
        <div class="channel-info">
            <img src="<?= $profile_pic ?>" alt="Profile Picture" class="channel-pfp">
            <div class="channel-title-block">
                <h1><?= $title ?></h1>
                <p><?= $subscribers ?> subscribers &bull; <?= $total_videos ?> videos &bull; <?= $total_views ?> views</p>
            </div>
        </div>
    </div>

    <div class="data-section-grid">
        <div class="data-card">
            <h3>Monetization Status</h3>
            <p class="status-<?= $is_monetized ? 'safe' : 'danger' ?>">
                <?= $is_monetized ? 'Likely Monetized' : 'Not Detected' ?>
            </p>
            <?php if ($is_monetized): ?>
                <small>Reasons: <?= implode(', ', $monetization_reasons) ?></small>
            <?php endif; ?>
        </div>
        <div class="data-card">
            <h3>Estimated Channel Earnings</h3>
            <p class="earnings-range">$<?= $earnings_low ?> - $<?= $earnings_high ?></p>
            <small>*Based on total views and industry RPM averages.</small>
        </div>
    </div>

    <h3>Recent Videos</h3>
    <div class="recent-videos-grid">
        <?php foreach ($recent_videos as $video): ?>
        <div class="video-card">
            <a href="https://www.youtube.com/watch?v=<?= $video['id']['videoId'] ?>" target="_blank">
                <img src="<?= htmlspecialchars($video['snippet']['thumbnails']['medium']['url']) ?>" alt="Video Thumbnail">
                <p class="video-title"><?= htmlspecialchars($video['snippet']['title']) ?></p>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
$output = ob_get_clean();
echo $output;
