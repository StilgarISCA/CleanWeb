<?php
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
   
   public function getSiteFeeds()
   {
      $elements = $this->_xPath->query( "/html/head/link[@type='application/rss+xml']" );
      
      if ( $elements->length > 0 )
         foreach ( $elements as $feedUrl ) {
            print 'Found ' . $feedUrl->getAttribute( 'href' ) .'<br>';
         }
         
         /*
      if( $elements->length > 0
          && ( $elements->item(0)->getAttribute( 'href' ) != ''
               || $elements->item(0)->getAttribute( 'href' ) == null )
      ) 
         return $elements->item(0)->getAttribute( 'href' ); 
         */
   } // end getSiteFeeds()
} // end SiteSyndication()

$foo = new SiteSyndication( "www.sltrib.com" );
$foo->getSiteFeeds();

?>