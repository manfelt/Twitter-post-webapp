<?php
	// inkluderer filer inn i scriptet. Fra 'innstillinger.php' skaffes verdier som 
	// uavhengig kan stilles inn ettersom nødvendig.
	// 'TwitterApiExchange.php' er en wrapper referert til av 'twitter developer' som 'Twitter-API-PHP': 
	// https://developer.twitter.com/en/docs/twitter-api/tools-and-libraries
	require_once('innstillinger.php');
	require_once('TwitterAPIExchange.php');
	
	// 'associative array', som 'mapper' nøkler fra innstillinger.php 
	$innstillinger = array('consumer_key' => TWITTER_API_KEY, 'consumer_secret' => TWITTER_API_SECRET, 
	'oauth_access_token' => TWITTER_ACCESS_TOKEN, 'oauth_access_token_secret' => TWITTER_ACCESS_TOKEN_SECRET );

	$postTilTwitter = 'asd';
	// URL 
	$url = 'https://api.twitter.com/1.1/statuses/update.json';
	$requestMethod = 'POST';
	$apiData = array( 'status' => $postTilTwitter );


	$twitter = new TwitterAPIExchange ( $innstillinger );
	$twitter->buildOauth( $url, $requestMethod );
	$twitter->setPostfields( $apiData );
	$response = $twitter->performRequest( true, array( CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0) );

	echo '<pre>';
	print_r(json_decode( $response, true) );
?>
<br><br>

<!-- input-felt for bruker. Twitter-poster skrives inn her. -->
<form action="index.php" method="post">
<input type="text" name="postTweet">
<input type="submit">

<br><br>

<h2>Tweet logg:</h2>
