<?php
class SiteIndexItem
{
   public $title;
   public $description;
   public $url;

/*   
   public function __construct( $title, $description, $url )
   {
      $this->title = $title;
      $this->description = $description;
      $this->url = $url;
   } // end ctor
*/
 
   public function __construct()
   {
      $this->title = NULL;
      $this->description = NULL;
      $this->url = NULL;
   }
}
?>