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
$accessToken = 'IGQWRNVmRGelJ6QndhcE41eVltVi1qUDNtZAElVMzRaNHEzaC1rem1DZA3loUlAzMnJLYUJBdHo5cm5aeXVnblJFN3ZACR2IxZAnRnWTUxMmUwXzd2cnhTREhoVUhLYVBiQ2FDaUlhTS1hdkY4b0k0RmJrWWQzRXVqNUEZD';
$urlParts = explode('/', $_POST['instgramurl']);
$reelIndex = array_search('reel', $urlParts);

if ($reelIndex !== false && isset($urlParts[$reelIndex + 1])) {
    // The media ID is the part of the URL after 'reel'
    $mediaId = $urlParts[$reelIndex + 1];
} else {
    echo "Media ID not found in URL.";
}

$url = "https://graph.instagram.com/{$mediaId}/comments?access_token={$accessToken}";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$comments = json_decode($response, true);

if (isset($comments['error'])) {
    echo 'Error: ' . $comments['error']['message'];
} else {
    $good_comments_data = array();
    $bad_comments_data = array();
    foreach ($comments['data'] as $comment) {
         $comment_status = classifyComment($comment);
       if($comment_status){
           array_push($good_comments_data, $comment['text']);
       }else{
           array_push($bad_comments_data, $comment['text']);
       }
    }
     echo "<pre> Good Comments : ";print_r(count($good_comments_data));
    echo "<pre> Bad Comments : ";print_r(count($bad_comments_data));
    echo "<pre> Good Comments Average : ";print_r(count($good_comments_data) / count($comments));
    echo "<pre> Bad Comments Average : ";print_r(count($bad_comments_data)  / count($comments));
}
?>
