<?php
require_once( './SiteIndexItem.inc' );

class SiteSyndication
{
   private $_url;

   public function getUrl()
   {
      return $this->_url;
   }

   private $_pageDom;
   private $_xPath;

   public function __construct( $url )
   {
      $this->_url = $url;
      $this->_pageDom = $this->loadPageDom ( $this->_url );
      $this->_xPath = new DomXPath( $this->_pageDom );
   } // end ctor

   private function loadPageDom( $url )
   {
      $domObj = new domDocument();
      libxml_use_internal_errors ( true );
      $domObj->loadHtmlFile ( $url );

      return $domObj;
   } // end loadPageDom()

   public function getSiteItems()
   {
      $feedUrl = $this->getFeeds();
   }

   public function getFeeds()
   {
      $elements = $this->_xPath->query ( "/html/head/link[@type='application/rss+xml']" );

      if ( $elements->length > 0
         && ( $elements->item ( 0 )->getAttribute ( 'href' ) != ''
            || $elements->item ( 0 )->getAttribute ( 'href' ) == NULL )
      )
         return $elements->item ( 0 )->getAttribute ( 'href' );
   } // end getFeeds()

   /***************************************************************************
    * Function: parse_rss_feed( str )
    * Accepts: rss data as xml format
    * Returns: array of SiteIndexItem objects
    *
    * Description:
    * Parses RSS (v2?) XML for the following: TITLE, DESCRIPTION, LINK, and stores
    * that information within an array of objects which is then returned.
    *
    * HTML is stripped from the discription, and the link is encoded using
    * urlencode and base64.
    ***************************************************************************/
   function parse_rss_feed( $xml_data )
   {
      // parse the xml into an array
      $xml_parser = xml_parser_create ();
      xml_parser_set_option ( $xml_parser, XML_OPTION_SKIP_WHITE, 1 );
      xml_parser_set_option ( $xml_parser, XML_OPTION_CASE_FOLDING, 1 );
      xml_parse_into_struct ( $xml_parser, $xml_data, $values );
      xml_parse ( $xml_parser, $xml_data );
      xml_parser_free ( $xml_parser );

      // loop through array pulling/formatting desired data, and throw into 2d array
      $cur_count = 0;
      for ( $i = 0; $i < sizeof ( $values ); $i++ ) {
         switch ( $values[ $i ]['tag'] ) {
            case "TITLE":
               $title = $values[ $i ]['value'];
               break;
            case "DESCRIPTION":
               // strip out any HTML/javascript garbage contaminating the feeds
               $description = strip_tags ( $values[ $i ]['value'] );
               break;
            case "LINK":
               // encode the url for easy passing through GET later
               $url = StringUtil::CleanWebEncode ( $values[ $i ]['value'] );
               break;
            case "ITEM":
               // add the item, increment and reinitialize
               $data_ary[ $cur_count++ ] = new SiteIndexItem( $title, $description, $url );
               $title = $description = $url = NULL;
               break;
         }
      }

      return $data_ary;
   } // end function parse_rss_feed()
} // end SiteSyndication()
?>