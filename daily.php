<?php

require_once('Dailymotion.php');

$apiKey = 'API_KEY_HERE';
$apiSecret = 'API_SECRET_HERE';

$api = new Dailymotion();      
$videoTitle = "Test video";     
$filePath = $argv[1];
$videoCategory = "fun";   

$api->setGrantType(Dailymotion::GRANT_TYPE_PASSWORD, $apiKey, $apiSecret, array('write','delete'), array('username' => USERNAME_HERE, 'password' => PASSWORD_HERE));  

$progressUrl = null;
$url = $api->uploadFile($filePath, null, $progressUrl);

echo 'Progress url:' . $progressUrl . "\r\n";

echo 'Video url: ' . $url . "\r\n";

// More fields may be mandatory in order to create a video.
// Please refer to the complete API reference for a list of all the required data.
$result = $api->post(
    '/me/videos',
    array('url' => $url, 'title' => $videoTitle)
);


?>
