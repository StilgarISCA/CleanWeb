<?php

/**
 * Encapsulates functionality for URL utilities
 */
class UrlUtil
{
   /**
    * Use fopen to make a connection to the url passed into the function, read
    * the page 4096 bytes at a time and return the contents.
    *
    * @param URL $url The URL to fetch
    *
    * @returns string site contents
    */
   public static function getUrlContents( $url )
   {
      if ( empty( $url ) )
         die( __METHOD__ . ': $url cannot be null or empty' );

      $fileContents = '';

      if ( !( $fileHandle = @fopen( $url, 'r' ) ) )
         die( __METHOD__ . ": Could not open: $url" );

      while ( !feof( $fileHandle ) )
         $fileContents .= fgets( $fileHandle, 4096 );

      fclose( $fileHandle );

      return $fileContents;
   } // end getUrlContents()
} // end UrlUtils