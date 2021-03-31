<?php 
    // Api-nøkler og 'access tokens' - første parameter; navn på string. Andre: verdi tilhørende string -altså nøkkel.
    define('TWITTER_API_KEY', 'Ezf2jzMNT48k5Q2hFAJJx8M4M');
    define('TWITTER_API_SECRET', 'Ykj0ma8vEgc6BuBujgY8KobviUUmcg4R4pLhCurAgTGTy00v2W');
    define('TWITTER_ACCESS_TOKEN', '1376912497052151812-deINMkH3zvibK48Ko08iI0ItKRSLi3');
    define('TWITTER_ACCESS_TOKEN_SECRET', 'sOFK0ejbhOERAGslcDPJANIu1ecSoFxxlK9ySTuDdVFuQ');

    // nedenfor burde kanskje også omgjøres til konstanter, slik som ovenfor. 
    // setter variabel for lengde på en tweet-post.
	$minimumTweetLengde = 20;
	$maksimumTweetLengde = 140;

    $ugyldigeKarakterer = array('Æ', 'Ø', 'Å', 'æ', 'ø', 'å')
	// setter antallet tweeter synlige i DOM.
	$antallSynligeTweeter = 10;

?>