<?php

/***
Some aweful manual tests to make sure things are behaving as expected.
*/
require_once("./SiteSyndication.inc");

echo "Get site feed for Google News<br>";
$siteSyndication = new SiteSyndication( "https://news.google.com" );
echo $siteSyndication->getFeeds();


?>