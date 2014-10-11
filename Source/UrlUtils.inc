<?php
/*
   Encapsulate functionality for URL utilities

   CleanWeb needs to be able to pass URLs through query strings to function. In
   order to prevent the web browser from parsing them or doubley-encoding or
   unencoding them, CleanWeb needs to have it's own encoding/decoding
   operations.
*/
class UrlUtils
{
   public static function CleanWebDecode( $encodedUrl )
   {
      return urldecode( base64_decode( $encodedUrl ) );
   } // end function CleanWebDecode()
   
   public static function CleanWebEncode( $urlToEncode )
   {
      return base64_encode( urlencode( $urlToEncode ) ); 
   } // end function CleanWebEncode()
}
?>