<?php

/**
 *  Encapsulates functionality for string utilities
 *
 *  CleanWeb needs to be able to pass URLs, and other strings as query params
 *  to function. In order to prevent the web browser from parsing them or
 *  doubley-encoding or unencoding them, CleanWeb needs to have its own
 *  encoding/decoding operations.
 */
class StringUtil
{
   /**
    * Decodes CleanWebEncoded Strings
    * @param string $encodedString CleanWeb encoded string to decode
    * @return string decoded string
    */
   public static function CleanWebDecode( $encodedString )
   {
      return urldecode ( base64_decode ( $encodedString ) );
   } // end function CleanWebDecode()

   /**
    * Encodes strings to CleanWeb format
    * @param string $stringToEncode string to encode
    * @return string encoded string
    */
   public static function CleanWebEncode( $stringToEncode )
   {
      return base64_encode ( urlencode ( $stringToEncode ) );
   } // end function CleanWebEncode()
}

?>