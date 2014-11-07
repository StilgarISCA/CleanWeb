<?php
require_once( './SiteIndexItem.inc' );
require_once( './UrlUtil.inc' );

define( 'ATOM_FEED_XPATH', "/html/head/link[@type='application/atom+xml']" );
define( 'RSS_FEED_XPATH', "/html/head/link[@type='application/rss+xml']" );

class SiteSyndication
{
   private $_url;

   public function GetUrl()
   {
      return $this->_url;
   }

   private $_pageDom;
   private $_xPath;

   public function __construct( $url )
   {
      if ( !isset( $url ) || is_null( $url ) || !trim( $url ) )
         throw new InvalidArgumentException( __METHOD__ . ': $url cannot be null, empty or whitespace' );

      $this->_url = $url;
      $this->_pageDom = self::LoadPageDom( $this->_url );
      $this->_xPath = new DomXPath( $this->_pageDom );
   } // end ctor

   private function LoadPageDom( $url )
   {
      $domObj = new domDocument();
      libxml_use_internal_errors( true );
      $domObj->loadHtmlFile( $url );

      return $domObj;
   } // end LoadPageDom()

   /**
    * Gets the items from the site's primary syndication feed
    * @return SiteIndexItem[] on success NULL otherwise
    */
   public function GetSiteIndexItems()
   {
      $siteIndexItemsAry = NULL;

      $rssUrl = $this->GetSyndicationUrl( RSS_FEED_XPATH );
      if ( !is_null( $rssUrl ) ) {
         $rssUrl = UrlUtil::EnsureFullUrl( $rssUrl, $this->_url );
         $rssFeed = UrlUtil::GetUrlContents( $rssUrl );
         $siteIndexItemsAry = SiteSyndication::ParseRssFeed( $rssFeed );
      }

      $atomUrl = $this->GetSyndicationUrl( ATOM_FEED_XPATH );
      if ( !is_null( $atomUrl ) ) {
         $atomUrl = UrlUtil::EnsureFullUrl( $atomUrl, $this->_url );
         $atomFeed = UrlUtil::GetUrlContents( $atomUrl );
         $siteIndexItemsAry = SiteSyndication::ParseAtomFeed( $atomFeed );
      }

      return $siteIndexItemsAry;
   } // end GetSiteIndexItems()

   /**
    * Locate the FIRST URL for a given xpath query in the page and return it
    *
    * @param $xpathQuery
    *
    * @return URL on success, NULL otherwise
    */
   public function GetSyndicationUrl( $xpathQuery )
   {
      $elements = $this->_xPath->query( $xpathQuery );

      if ( $elements->length > 0
         && ( $elements->item( 0 )->getAttribute( 'href' ) != ''
            || $elements->item( 0 )->getAttribute( 'href' ) == NULL )
      )
         return $elements->item( 0 )->getAttribute( 'href' );
   } // end GetSyndicationUrl()

   /**
    * Parses Atom XML for the elements which can be used for a title, description and link,
    * and stores that information within an array of objects which is then returned.
    *
    * HTML is stripped from the description, and the link is encoded using
    * urlencode and base64.
    *
    * @param XML $atomXml XML from RSS feed
    *
    * @return SiteIndexItem[]
    */
   public function ParseAtomFeed( $atomXml )
   {
      $atomFeedAry = self::XmlToArray( $atomXml );

      // loop through array pulling/formatting desired data, and throw into array
      $title = $description = $url = NULL;
      $curCount = 0;
      for ( $i = 0; $i < sizeof( $atomFeedAry ); $i++ ) {
         switch ( $atomFeedAry[ $i ]['tag'] ) {
            case "TITLE":
               $title = $atomFeedAry[ $i ]['value'];
               break;
            case "SUBTITLE":
               // strip out any HTML/javascript garbage contaminating the feeds
               $subtitle = strip_tags( $atomFeedAry[ $i ]['value'] );
               break;
            case "SUMMARY":
               // strip out any HTML/javascript garbage contaminating the feeds
               $summary = strip_tags( $atomFeedAry[ $i ]['value'] );
               break;
            case "CONTENT":
               // strip out any HTML/javascript garbage contaminating the feeds
               $content = strip_tags( $atomFeedAry[ $i ]['value'] );
               break;
            case "LINK":
               $url = $atomFeedAry[ $i ]['attributes']['HREF'];
               $url = UrlUtil::EnsureFullUrl( $url, $this->_url );
               // encode the url for easy passing through GET later
               $url = StringUtil::CleanWebEncode( $url );
               break;
            case "ENTRY":
               // Set the description to the longest value, description, subtitle or content
               if ( strlen( $subtitle ) > strlen( $summary ) )
                  $description = $subtitle;
               else
                  $description = $summary;
               if ( strlen( $content ) > strlen( $description ) )
                  $description = $content;

               // strip out any HTML/javascript garbage contaminating the feeds
               $description = strip_tags( $description );

               // add the item, increment and reinitialize
               $siteItems[ $curCount++ ] = new SiteIndexItem( $title, $description, $url );
               $title = $description = $subtitle = $summary = $content = $url = NULL;
               break;
         }
      }

      return $siteItems;
   } // end ParseAtomFeed()

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
   public function ParseRssFeed( $rssXml )
   {
      $rssFeedAry = self::XmlToArray( $rssXml );

      // loop through array pulling/formatting desired data, and throw into array
      $title = $description = $url = NULL;
      $curCount = 0;
      for ( $i = 0; $i < sizeof( $rssFeedAry ); $i++ ) {
         switch ( $rssFeedAry[ $i ]['tag'] ) {
            case "TITLE":
               $title = $rssFeedAry[ $i ]['value'];
               break;
            case "DESCRIPTION":
               // strip out any HTML/javascript garbage contaminating the feeds
               $description = strip_tags( $rssFeedAry[ $i ]['value'] );
               break;
            case "LINK":
               $url = $rssFeedAry[ $i ]['value'];
               $url = UrlUtil::EnsureFullUrl( $url, $this->_url );
               // encode the url for easy passing through GET later
               $url = StringUtil::CleanWebEncode( $url );
               break;
            case "ITEM":
               // add the item, increment and reinitialize
               $siteItems[ $curCount++ ] = new SiteIndexItem( $title, $description, $url );
               $title = $description = $url = NULL;
               break;
         }
      }

      return $siteItems;
   } // end ParseRssFeed()

   /**
    * Converts XML into an array
    *
    * @param $xml
    *
    * @return mixed[]
    */
   private static function XmlToArray( $xml )
   {
      $xmlParser = xml_parser_create();
      xml_parser_set_option( $xmlParser, XML_OPTION_SKIP_WHITE, 1 );
      xml_parser_set_option( $xmlParser, XML_OPTION_CASE_FOLDING, 1 );
      xml_parse_into_struct( $xmlParser, $xml, $output );
      xml_parse( $xmlParser, $xml );
      xml_parser_free( $xmlParser );

      return $output;
   }
} // end SiteSyndication()
?>