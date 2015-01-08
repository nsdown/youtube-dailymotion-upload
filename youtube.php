<?php

$OAUTH2_CLIENT_ID = 'CLIENT_ID';
$OAUTH2_CLIENT_SECRET = 'CLIENT_SECRET';

require_once('autoload.php');

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setRedirectUri('http://localhost/'); // Incorrecte
$client->setScopes('https://www.googleapis.com/auth/youtube');

// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

$authUrl = $client->createAuthUrl();

echo 'Go to ' . $authUrl;

$h = fopen("php://stdin", "r");
$code = fgets($h);
fclose($h);

$client->authenticate($code);

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) 
{
	try {
		$videoPath = $argv[1];
		//$videoPath = "../youtube-upload/ACU_1.mp4";
		// Create a snippet with title, description, tags and category ID
	    // Create an asset resource and set its snippet metadata and type.
	    // This example sets the video's title, description, keyword tags, and
	    // video category.
	    $snippet = new Google_Service_YouTube_VideoSnippet();
	    $snippet->setTitle("Test title");
	    $snippet->setDescription("Test description");
	    $snippet->setTags(array("tag1", "tag2"));

	    // Numeric video category. See
	    // https://developers.google.com/youtube/v3/docs/videoCategories/list 
	    $snippet->setCategoryId("22");

	    // Set the video's status to "public". Valid statuses are "public",
	    // "private" and "unlisted".
	    $status = new Google_Service_YouTube_VideoStatus();
	    $status->privacyStatus = "unlisted";

	    // Associate the snippet and status objects with a new video resource.
	    $video = new Google_Service_YouTube_Video();
	    $video->setSnippet($snippet);
	    $video->setStatus($status);

	    // Specify the size of each chunk of data, in bytes. Set a higher value for
	    // reliable connection as fewer chunks lead to faster uploads. Set a lower
	    // value for better recovery on less reliable connections.
	    $chunkSizeBytes = 5 * 1024 * 1024;

	    // Setting the defer flag to true tells the client to return a request which can be called
	    // with ->execute(); instead of making the API call immediately.
	    $client->setDefer(true);

	     // Create a request for the API's videos.insert method to create and upload the video.
	    $insertRequest = $youtube->videos->insert("status,snippet", $video);

	    // Create a MediaFileUpload object for resumable uploads.
	    $media = new Google_Http_MediaFileUpload(
	        $client,
	        $insertRequest,
	        'video/*',
	        null,
	        true,
	        $chunkSizeBytes
	    );

	    $fileSize = filesize($videoPath);
	    $media->setFileSize($fileSize);

	    // Read the media file and upload it chunk by chunk.
	    $status = false;
	    $handle = fopen($videoPath, "rb");

	    echo "Uploading to Youtube \r\n";

	    while (!$status && !feof($handle)) 
	    {
	    	$chunk = fread($handle, $chunkSizeBytes);
	      	$status = $media->nextChunk($chunk);
	      	echo 'Progress: ' . round(100 * $media->getProgress() / $fileSize) . " % \r";
	    }

	    fclose($handle);

	    // If you want to make other calls after the file upload, set setDefer back to false
    	$client->setDefer(false);

    	echo 'Video uploaded to Youtube: http://youtu.be/' . $status['id'] . "\n";
	}
	catch (Google_ServiceException $e) 
	{
		echo(sprintf('<p>A service error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage())));
	} 
	catch (Google_Exception $e) 
	{
		echo(sprintf('<p>An client error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage())));
	}
}
else
{

}

?>
