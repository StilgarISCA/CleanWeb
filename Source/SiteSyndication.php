<?php
class SiteSyndication
{
   private $_url;
   
   public function getUrl()
   {
      return $this->$_url;
   }
   
   private $_pageDom;
   private $_xPath;
   
   public function __construct( $url )
   {
      $this->$_url = $url;      
      $this->$_pageDom = loadPageDom( $this->$_url );      
      $this->$_xPath = new DomXPath( $this->$_pageDom );
   }
   
   private function loadPageDom( $url );
   {
      $domObj = new domDocument();
      libxml_use_internal_errors( true );
      $domObj->loadHtmlFile( $url );      
   }
   
   public function getSiteFeeds()
   {
      $elements = $xpath->query( "/html/head/link[@type='application/rss+xml']" );
      if(    $elements->length > 0
          && ( $elements->item(0)->getAttribute( 'href' ) != ''
          || $elements->item(0)->getAttribute( 'href' ) == null )
      )
         return $elements->item(0)->getAttribute( 'href' ); 
   }
}
?>