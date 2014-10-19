<?php
require_once( './SiteIndexItem.inc' );
require_once( './UrlUtil.inc' );
require_once( './StringUtil.inc' );

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
      $this->_pageDom = $this->loadPageDom( $this->_url );
      $this->_xPath = new DomXPath( $this->_pageDom );
   } // end ctor

   private function loadPageDom( $url )
   {
      $domObj = new domDocument();
      libxml_use_internal_errors( true );
      $domObj->loadHtmlFile( $url );

      return $domObj;
   } // end loadPageDom()

   /**
    * @return SiteIndexItem[] on success NULL otherwise
    */
   public function getSiteIndexItems()
   {
      $siteIndexItemsAry = NULL;
      $rssUrl = $this->getRssFeedUrl();

      if ( $rssUrl != NULL ) {
         $rssFeed = UrlUtil::GetUrlContents( $rssUrl );
         $siteIndexItemsAry = SiteSyndication::parseRssFeed( $rssFeed );
      }

      return $siteIndexItemsAry;
   } // end getSiteIndexItems()

   public function getRssFeedUrl()
   {
      $elements = $this->_xPath->query( "/html/head/link[@type='application/rss+xml']" );

      if( $elements->length > 0
         && ( $elements->item(0)->getAttribute( 'href' ) != ''
            || $elements->item(0)->getAttribute( 'href' ) == NULL )
      )
         return $elements->item(0)->getAttribute( 'href' );
   } // end getRssFeedUrl()

   /**
    * Parses RSS (v2?) XML for the following: TITLE, DESCRIPTION, LINK, and stores
    * that information within an array of objects which is then returned.
    *
    * HTML is stripped from the description, and the link is encoded using
    * urlencode and base64.
    *
    * @param XML $rssXml XML from RSS feed
    *
    * @return SiteIndexItem[]
    */
   public static function parseRssFeed( $rssXml )
   {
      // parse the xml into an array
      $xmlParser = xml_parser_create();
      xml_parser_set_option( $xmlParser, XML_OPTION_SKIP_WHITE, 1 );
      xml_parser_set_option( $xmlParser, XML_OPTION_CASE_FOLDING, 1 );
      xml_parse_into_struct( $xmlParser, $rssXml, $values );
      xml_parse( $xmlParser, $rssXml );
      xml_parser_free( $xmlParser );

      // loop through array pulling/formatting desired data, and throw into array
      $title = $description = $url = NULL;
      $curCount = 0;
      for ( $i = 0; $i < sizeof( $values ); $i++ ) {
         switch ( $values[ $i ]['tag'] ) {
            case "TITLE":
               $title = $values[ $i ]['value'];
               break;
            case "DESCRIPTION":
               // strip out any HTML/javascript garbage contaminating the feeds
               $description = strip_tags( $values[ $i ]['value'] );
               break;
            case "LINK":
               // encode the url for easy passing through GET later
               $url = StringUtil::CleanWebEncode( $values[ $i ]['value'] );
               break;
            case "ITEM":
               // add the item, increment and reinitialize
               $siteItems[ $curCount++ ] = new SiteIndexItem( $title, $description, $url );
               $title = $description = $url = NULL;
               break;
         }
      }

      return $siteItems;
   } // end praseRssFeed()
} // end SiteSyndication()
?>