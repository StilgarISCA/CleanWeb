<?php
require_once( './StringUtil.inc' );

$urlToEncode = 'http://www.php.net/';
$encodedUrl = 'aHR0cCUzQSUyRiUyRnd3dy5waHAubmV0JTJG';

print "Url: $urlToEncode as encoded is: " . UrlUtils::CleanWebEncode( $urlToEncode ) . '<br>';
print "Encoded string: $encodedUrl decoded is: " . UrlUtils::CleanWebDecode( $encodedUrl ) . '<br>';
?>