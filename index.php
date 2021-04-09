<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stil.css">
    <title>Twitter-API</title>
</head>
<body>

    <!-- input-felt for bruker. Twitter-poster skrives inn her. -->
    <form action="sendTilTwitter.php" method="post">
    <input type="text" name="postTweet">
    <input type="submit"> 

    <br><br>
    <div class="feiltekst">

    <!-- Evt feilmeldinger er ført over fra 'sendTilTwitter.php' hit til index, 
    med bruk av SESSIONS. Det samme med twittermelding publisert i formen. Begge logges i konsoll.-->
    <?php 
    // 'session_start()' kreves for at '$_SESSION[]' skal virke.
    session_start();
    $feilTekst = $_SESSION['feilTekst'];
    if (isset($_SESSION['feilTekst'])) {
        echo "<script>console.log(' Feilmelding: $feilTekst ');</script>";
        echo "<p>" . $feilTekst . "</p>";
        unset($_SESSION['feilTekst']);
    }
    $tweetTilKonsoll = $_SESSION['tweetTilKonsoll'];
    if (isset($_SESSION['tweetTilKonsoll'])) {
        echo "<script>console.log(' Du skrev: $tweetTilKonsoll ');</script>";
        unset($_SESSION['tweetTilKonsoll']);
    }
    ?> 

    </div>

    <br>

    <h2>Tweet logg:</h2>
    
    <?php
    // Åpner JSON fil, hvor tweeter er lagret. Som så dekodes for å senere kunne gå gjennom innholdet.
    $åpneJsonFil= file_get_contents('logg.json');
    $dekodetJsonFil = json_decode($åpneJsonFil, true);

	// setter antallet tweeter synlige i DOM.
	$antallSynligeTweeter = 10;

    // teller opp antallet tweets lagret i JSON loggen.
    $antallTweeter = count($dekodetJsonFil['tweeter']);

    // sjekker om antallet tweets er større enn/lik antallet tweets som skal være synlige,
    // deretter settes start indeksen som brukes i foreach blokken under. Starten på indeksen
    // skal være de x antall siste tweeter fra JSON filen definert i $antallSynligeTweeter.
    if ($antallTweeter >= $antallSynligeTweeter) {
        $indeksStartPosisjon = $antallTweeter - $antallSynligeTweeter;
    } else {
        $indeksStartPosisjon = 0;
    }

    // i foreach løkken brukes 'array_slice()' for å kutte rett til en indeksert plass i listen.
    // deretter printer den alle relevante verdier for hver x-te twitterpost.
    foreach ( array_slice($dekodetJsonFil['tweeter'], $indeksStartPosisjon) as $tweet ) : ?>
        <div class = "<?php echo $tweet['type']?>">
        <img src="<?php echo $tweet['bilde']; ?>" />
        <a href="https://twitter.com/<?php echo $tweet['navn']; ?>" target="_blank">
            <b>@<?php echo $tweet['navn']; ?></b>
        </a> skrev:
        <br />
        <br />
        <?php echo $tweet['tekstinnhold']; ?>
        <br />
        <br />
        Skrevet <?php echo $tweet['laget']; ?>, GMT.
        <br />
        <?php if (array_key_exists('feiltype', $tweet)) {
            echo "feilmelding: " . $tweet['feiltype']; } ?>
        <hr />
        </div>
    <?php endforeach; ?>

</body>
</html>
<br><br>


