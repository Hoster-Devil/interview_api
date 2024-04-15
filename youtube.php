<?php
$positive_words = ["good", "great", "excellent", "awesome"];
$negative_words = ["bad", "terrible", "awful", "horrible"];
function evaluateComment($comment) {
    global $positive_words, $negative_words;
    $score = 0;
    $words = explode(" ", strtolower($comment));
    foreach ($words as $word) {
        if (in_array($word, $positive_words)) {
            $score += 1;
        } elseif (in_array($word, $negative_words)) {
            $score -= 1;
        }
    }
    return $score;
}

function classifyComment($comment, $threshold = 0) {
    $score = evaluateComment($comment);
    if ($score >= $threshold) {
        return 1;
    } else {
        return 0;
    }
}
// Set your API key here
$apiKey = 'AIzaSyBJtv22ZJ9zgJjbci504NllxAC1lRKk944';

// Video ID for which you want to fetch comments
// Extract video ID from the URL
// echo "<pre> Good Comments : ";print_r($_POST);
parse_str(parse_url($_POST['videourl'], PHP_URL_QUERY), $url_params);
$videoId = $url_params['v'];


// URL for making request to YouTube Data API
$url = 'https://www.googleapis.com/youtube/v3/commentThreads';

// Parameters for the request
$params = array(
    'part' => 'snippet',
    'videoId' => $videoId,
    'key' => $apiKey,
    'maxResults' => 1000
);

// Build URL with parameters
$url .= '?' . http_build_query($params);

$response = file_get_contents($url);

$data = json_decode($response, true);

// Check if response contains errors
if (isset($data['error'])) {
    echo 'Error: ' . $data['error']['message'];
} else {
    // Extract comments from response
    $comments = array();
    foreach ($data['items'] as $item) {
        $comment = $item['snippet']['topLevelComment']['snippet']['textDisplay'];
        $comments[] = $comment;
    }
    $good_comments_data = array();
    $bad_comments_data = array();
    // Output comments
    foreach ($comments as $comment) {
       $comment_status = classifyComment($comment);
       if($comment_status){
           array_push($good_comments_data, $comment);
       }else{
           array_push($bad_comments_data, $comment);
       }
    }

    echo "<pre> Good Comments : ";print_r(count($good_comments_data));
    echo "<pre> Bad Comments : ";print_r(count($bad_comments_data));
    echo "<pre> Good Comments Average : ";print_r(count($good_comments_data) / count($comments));
    echo "<pre> Bad Comments Average : ";print_r(count($bad_comments_data)  / count($comments));
}

?>
