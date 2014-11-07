<?php

/**
 *  Encapsulates functionality for string utilities
 */
class StringUtil
{
   /**
    *  CleanWeb needs to be able to pass URLs, and other strings as query params
    *  to function. In order to prevent the web browser from parsing them or
    *  doubley-encoding or unencoding them, CleanWeb needs to have its own
    *  encoding/decoding operations.
    */

   /**
    * Decodes CleanWebEncoded Strings
    *
    * @param string $encodedString CleanWeb encoded string to decode
    *
    * @return string decoded string
    */
   public static function CleanWebDecode( $encodedString )
   {
      return urldecode( base64_decode( $encodedString ) );
   } // end CleanWebDecode()

   /**
    * Encodes strings to CleanWeb format
    *
    * @param string $stringToEncode string to encode
    *
    * @return string encoded string
    */
   public static function CleanWebEncode( $stringToEncode )
   {
      return base64_encode( urlencode( $stringToEncode ) );
   } // end CleanWebEncode()

   /**
    * Check to see if a string starts with another string
    * Adapted from:
    *  http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
    *
    * @param string $haystack string to search in
    * @param string $needle string to search for
    *
    * @return bool true on match, false otherwise
    */
   public static function StartsWith( $haystack, $needle )
   {
      return !strncmp( $haystack, $needle, strlen( $needle ) );
   } // end StartsWith()

}

?>