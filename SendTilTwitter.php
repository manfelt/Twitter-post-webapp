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

	// URL 
	$url = 'https://api.twitter.com/1.1/statuses/update.json';

	// bestemmer request-type
	$requestMethode = 'POST';

	// verdien til denne variabelen er innholdet som postes og sendes til twitter. 
    // Den skaffes direkte fra input feltet hos 'index.php'
	$postTilTwitter = $_POST["postTweet"];

    $postTilTwitter_arr = str_split($postTilTwitter);

    foreach ($postTilTwitter_arr as $twitterPost) {
        if ( in_array($twitterpost, $forbudteKarakterer )) {
            echo 'Tweet inneholder ugyldige karakterer';
            break;
        }
    }

    if (strlen($postTilTwitter) < $minimumTweetLengde) {
        echo 'Tweet er for kort';
    } elseif (strlen($postTilTwitter) > $maksimumTweetLengde) {
        echo 'Tweet er for lang';
    }


    // 'associative array' som tar vare på twitter-post innholdet som skal sendes.
	$apiData = array( 'status' => $postTilTwitter );

	$twitter = new TwitterAPIExchange ( $innstillinger );
	$twitter->buildOauth( $url, $requestMethode );
	$twitter->setPostfields( $apiData );
	$response = $twitter->performRequest( true, array( CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0) );

	// <pre> - uformattert tekst
	echo '<pre>';
	print_r(json_decode( $response, true) );
	
?>