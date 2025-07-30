<?php
/**
 * YouTube Shadowban Detector
 * 
 * This tool attempts to detect if a channel is shadowbanned.
 */

require_once 'config.php';

// Function to extract channel ID from various input types
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

// First, get the exact channel title
$channel_url = "https://www.googleapis.com/youtube/v3/channels?part=snippet&id=" . $channel_id . "&key=" . $api_key;
$channel_response = @file_get_contents($channel_url);
if ($channel_response === false || empty(json_decode($channel_response, true)['items'])) {
    echo '<div class="result-card error"><p>Could not retrieve channel details.</p></div>';
    exit;
}
$channel_data = json_decode($channel_response, true)['items'][0];
$channel_title = $channel_data['snippet']['title'];

// Now, search for the channel by its exact title
$search_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . urlencode('"' . $channel_title . '"') . "&type=channel&maxResults=10&key=" . $api_key;
$search_response = @file_get_contents($search_url);
if ($search_response === false) {
    echo '<div class="result-card error"><p>Could not perform search to verify channel visibility.</p></div>';
    exit;
}

$search_data = json_decode($search_response, true);
$found_in_search = false;
if (!empty($search_data['items'])) {
    foreach ($search_data['items'] as $item) {
        if ($item['snippet']['channelId'] === $channel_id) {
            $found_in_search = true;
            break;
        }
    }
}

$status = $found_in_search ? 'No Shadowban Detected' : 'Potential Shadowban Detected';
$status_class = $found_in_search ? 'status-safe' : 'status-danger';
$explanation = $found_in_search 
    ? 'This channel appears in search results for its name, which is a good sign.' 
    : 'This channel does not appear in the top search results for its exact name. This could be an indicator of a shadowban, meaning its visibility may be restricted by YouTube.';

$output = "
<div class='result-card'>
    <div class='result-header'>
        <h3 class='result-title'>Shadowban Test for " . htmlspecialchars($channel_title) . "</h3>
    </div>
    <div class='result-content'>
        <div class='shadowban-status {$status_class}'>
            <h4>{$status}</h4>
        </div>
        <p>{$explanation}</p>
    </div>
</div>";

echo $output;
