<?php
require_once( './StringUtil.inc' );

/**
 * Encapsulates functionality for URL utilities
 */
class UrlUtil
{
   /**
    * Make sure that a given URL part starts with a FQDN
    *
    * @param $urlPart URL part to test
    * @param $baseUrl base URL
    *
    * @return URL
    */
   public static function ensureFullUrl( $urlPart, $baseUrl )
   {
      if ( empty( $urlPart ) )
         throw new InvalidArgumentException( __METHOD__ . ':$urlPart cannot be null or empty' );

      if ( empty( $baseUrl ) )
         throw new InvalidArgumentException( __METHOD__ . ':$baseUrl cannot be null or empty' );

      $returnValue = $urlPart;

      if ( StringUtil::StartsWith( $urlPart, '/' ) )
         $returnValue = $baseUrl . $urlPart;

      return $returnValue;
   } // end ensureFullUrl()

   /**
    * Use fopen to make a connection to the URL passed into the function, read
    * the page 4096 bytes at a time and return the contents.
    *
    * @param URL $url The URL to fetch
    *
    * @returns string site contents
    */
   public static function getUrlContents( $url )
   {
      if ( empty( $url ) )
         throw new InvalidArgumentException( __METHOD__ . ': $url cannot be null or empty' );

      $fileContents = '';

      if ( !( $fileHandle = @fopen( $url, 'r' ) ) )
         throw new InvalidArgumentException( __METHOD__ . ": Could not open: $url" );

      while ( !feof( $fileHandle ) )
         $fileContents .= fgets( $fileHandle, 4096 );

      fclose( $fileHandle );

      return $fileContents;
   } // end getUrlContents()
} // end UrlUtils