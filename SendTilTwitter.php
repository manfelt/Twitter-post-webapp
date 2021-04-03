<?php
	// inkluderer filer inn i scriptet. Fra 'innstillinger.php' skaffes verdier som 
	// uavhengig kan stilles inn ettersom nødvendig.
	// 'TwitterApiExchange.php' er en wrapper referert til av 'twitter developer' som 'Twitter-API-PHP': 
	// https://developer.twitter.com/en/docs/twitter-api/tools-and-libraries
	require('innstillinger.php');
	require_once('TwitterAPIExchange.php');


	// verdien til denne variabelen er innholdet som postes og sendes til twitter. 
    // Den skaffes direkte fra input feltet hos 'index.php'
	$postTilTwitter = $_POST['postTweet'];

	
	function evaluerTwitterPost ($postTilTwitter){
		$tweetLengde = strlen($postTilTwitter);
		$ugyldigeKarakterer = false;
		$minimumTweetLengde = 20;
		$maksimumTweetLengde = 140;
		$forbudteKarakterer = array('Æ', 'Ø', 'Å', 'æ', 'ø', 'å');

		// funksjon som splitter en streng til en array. Nødvendig ettersom str_splint() ikke tar hensyn til unicode.
		function splittStrengTilUnicode($streng, $lengde = 1) {
			$strengSplitt = preg_split('~~u', $streng, -1, PREG_SPLIT_NO_EMPTY);
			if ($lengde > 1) {
				$karakter = array_chunk($strengSplitt, $lengde);
				
				foreach ($karakter as $i => $karakter) {
					$karakter[$i] = join('', (array) $karakter);
				}
				$strengSplitt = $karakter;
			}
			return $strengSplitt;
		}

		$postTilTwitterKarakterer = splittStrengTilUnicode($postTilTwitter);

		foreach ($postTilTwitterKarakterer as $karakter) {
			if ( in_array($karakter, $forbudteKarakterer )) {
				$ugyldigeKarakterer = true;
				break;
			}
		}
		if ($tweetLengde < $minimumTweetLengde) {
			echo 'Tweet er for kort <script>console.log("Tweet er for kort");</script>';
		} elseif ($tweetLengde > $maksimumTweetLengde) {
			echo 'Tweet er for lang <script>console.log("Tweet er for lang");</script>';
		} elseif ($ugyldigeKarakterer) {
			echo 'Tweet inneholder ugyldige karakterer <script>console.log("Tweet inneholder ugyldige karakterer");</script>';
		} else {

				// URL - Ikke endre denne, om mindre man skal gjøre annet enn å opprette twitterposter.
				$url = 'https://api.twitter.com/1.1/statuses/update.json';

				// bestemmer request-type
				$requestMethode = 'POST';

				// 'associative array', som 'mapper' nøkler fra innstillinger.php 
				$innstillinger = array('consumer_key' => TWITTER_API_KEY, 'consumer_secret' => TWITTER_API_SECRET, 
				'oauth_access_token' => TWITTER_ACCESS_TOKEN, 'oauth_access_token_secret' => TWITTER_ACCESS_TOKEN_SECRET );
			    // 'associative array' som tar vare på twitter-post innholdet som skal sendes.
				$apiData = array( 'status' => $postTilTwitter );

				$twitter = new TwitterAPIExchange ( $innstillinger );
				$twitter->buildOauth( $url, $requestMethode );
				$twitter->setPostfields( $apiData );
				$response = $twitter->performRequest( true, array( CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0) );

				// <pre> - uformattert tekst
				// echo '<pre>';
				// print_r(json_decode( $response, true) );
				$lagretTweet = json_decode( $response, true);

				return $lagretTweet;
			}
			
		}

	$lagretTweet = evaluerTwitterPost ($postTilTwitter);

?>
<script>console.log("<?php echo $lagretTweet; ?>");</script>

<h2>Tweet logg:</h2>

	<img src="<?php echo $lagretTweet['user']['profile_image_url']; ?>" />

	<a href="https://twitter.com/<?php echo $lagretTweet['user']['screen_name']; ?>" target="_blank">
	<b>@<?php echo $lagretTweet['user']['screen_name']; ?></b>
	</a>
	
	<!-- dato på tweet -->
	<p><?php echo $lagretTweet['created_at']; ?></p>
	<br />

	<p>Tekstinnhold:</p>
	<br />
	<?php echo $lagretTweet['text']; ?>
	<br />