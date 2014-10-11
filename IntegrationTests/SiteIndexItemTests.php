<?php

/***
Some aweful manual tests to make sure things are behaving as expected.
*/
require_once("./SiteIndexItem.inc");

$title = "Test Title";
$description = "fake stuff here";
$url = "http://www.test.com/";

$SiteIndexItem = new SiteIndexItem( $title, $description, $url );
echo 'Title: ' . $SiteIndexItem->title;
echo 'Description: ' . $SiteIndexItem->description;
echo 'Url: ' . $SiteIndexItem->url;

?>