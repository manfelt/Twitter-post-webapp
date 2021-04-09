<?php
	// 'session_start()' kreves for at '$_SESSION[]' skal virke.
	session_start();
	// inkluderer filer inn i scriptet. Fra 'innstillinger.php' skaffes verdier som 
	// uavhengig kan stilles inn ettersom nødvendig.
	// 'TwitterApiExchange.php' er en wrapper referert til av 'twitter developer' som 'Twitter-API-PHP': 
	// https://developer.twitter.com/en/docs/twitter-api/tools-and-libraries
	require_once('innstillinger.php');
	require_once('TwitterAPIExchange.php');

	// verdien til denne variabelen er innholdet som postes og sendes til twitter. 
    // Den skaffes direkte fra input feltet hos 'index.php'
	$postTilTwitter = $_POST['postTweet'];
	
	// Den største funksjonen i webappen. Tar for seg flere ansvarsroller enn det den i ettertid burde.
	// Bør kutes inn i mindre funksjoner for egne tilpassede nytter, som f.eks en egen funksjon for JSON lagring.
	function evaluerTwitterPost ($postTilTwitter) {
		// setter session for index. Returnerer twitterposten for at den skal console logge den effektivt.
		$_SESSION['tweetTilKonsoll'] = $postTilTwitter;
		// integer for å holde styr på lengden i tweeten når den sammenliknes med krav.
		$tweetLengde = strlen($postTilTwitter);
		$ugyldigeKarakterer = false;
		$minimumTweetLengde = 20;
		$maksimumTweetLengde = 140;
		$forbudteKarakterer = array('Æ', 'Ø', 'Å', 'æ', 'ø', 'å');

		// funksjon som splitter en streng til en array. Nødvendig ettersom str_splint() ikke tar hensyn til unicode. Regex.
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

		// twitterposten spaltes inn i matrise.
		$postTilTwitterKarakterer = splittStrengTilUnicode($postTilTwitter);

		// funksjon som spesifikt lagrer feilmeldinger inn i JSON databasen. 
		// 1. JSON filen åpnes og dekodes, 2. en assosiativ matrise tar imot verdier fra twitter responsen.
		// 3. '$data' som er hele JSON filen dekodet, blir pushet med de nye matriseverdiene. Alt lagres tilbake til JSON fil.
		function lagreFeilmeldingTilJson($feilType, $postTilTwitter) {
			$fil = file_get_contents('logg.json');
			$data = json_decode($fil, true);

			$matrise = array("type"=>"feilmelding", "laget"=>date('d m H:i:s Y'),
			"tekstinnhold"=>$postTilTwitter, "bilde"=>' ', "feiltype"=>$feilType, "navn"=> ' ');

			$data["tweeter"] = array_values($data["tweeter"]);
			array_push($data["tweeter"], $matrise);
			file_put_contents("logg.json", json_encode($data, JSON_UNESCAPED_UNICODE) );
			
			// for å evt. tilbakegi feilmelding til 'index' for console logging.
			$_SESSION['feilTekst'] = $feilType;
		}

		// går gjennom twitterposten og sjekker for ugyldige karakterer.
		foreach ($postTilTwitterKarakterer as $karakter) {
			if ( in_array($karakter, $forbudteKarakterer )) {
				$ugyldigeKarakterer = true;
				break;
			}
		} // sjekker om twitterposten går gjennom kriteriene. 
		if ($tweetLengde < $minimumTweetLengde) {
			$feilType = "Tweet er for kort ";
			lagreFeilmeldingTilJson($feilType, $postTilTwitter);
		} elseif ($tweetLengde > $maksimumTweetLengde) {
			$feilType = "Tweet er for lang ";
			lagreFeilmeldingTilJson($feilType, $postTilTwitter);
		} elseif ($ugyldigeKarakterer) {
			$feilType = "Tweet inneholder ugyldige karakterer  ";
			lagreFeilmeldingTilJson($feilType, $postTilTwitter);
		} else {
				//Inne i denne blokken utføres selve API-behandlingen.

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

				// responsen fra API-behandlingen ligger lagret i denne variabelen.
				$lagretTweet = json_decode($response, true);

				// sjekker om svaret fra twitter inneholder en feilmelding, hvis ikke går scriptet videre
				// til å lagre twittermeldingen i JSON logg.
				if(array_key_exists("errors", $lagretTweet)) {
					$feilType = $lagretTweet['errors']['0']['message'];
					echo $feilType . "<script>console.log('$feilType');</script>";
					lagreFeilmeldingTilJson($feilType, $postTilTwitter);
				} else {
					$fil = file_get_contents('logg.json');
					$data = json_decode($fil, true);
	
					$matrise = array("type"=>"post", "laget"=>$lagretTweet['created_at'],
					"tekstinnhold"=>$lagretTweet['text'], "bilde"=>$lagretTweet['user']['profile_image_url'],
					"navn"=>$lagretTweet['user']['screen_name']);
	
					$data["tweeter"] = array_values($data["tweeter"]);
					array_push($data["tweeter"], $matrise);
					file_put_contents("logg.json", json_encode($data) );
				}
				
				return $lagretTweet;
			}
			
		}

	$lagretTweet = evaluerTwitterPost ($postTilTwitter);

	// omdirigerer tilbake til index.php.
	header("Location: index.php");

?>
