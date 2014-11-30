<?php
/***************************************************************************
 * Program: cleanweb/index.php
 * Authors: Glenn Hoeppner
 * Date Started: 7/17/2013
 * Date Completed:
 *
 * Description:
 *
 * Parse RSS Feeds and display the list of descriptions/links.  Links clicked
 * by the user are "cleaned" and sent back to the client.
 ***************************************************************************/
// Set the timezone for the script
// http://php.net/manual/en/timezones.php
date_default_timezone_set( 'America/Detroit' );

define( 'HOST_DOMAIN', 'http://' . $_SERVER['SERVER_NAME'] );
define( 'DEFAULT_BASE_URL', 'http://news.google.com' );

require_once( './SiteSyndication.inc' );
require_once( './SiteIndexItem.inc' );
require_once( './StringUtil.inc' );
require_once( './Template.inc' );
require_once( './UrlUtil.inc' );

if ( isset( $_GET['perform'] ) && $_GET['perform'] == "getpage" ) {
   // Get a single page
   $url = StringUtil::CleanWebDecode( $_GET['page'] );
   $htmlPage = UrlUtil::GetUrlContents( $url );
   $cleanedPage = clean_html_page( $htmlPage );
   $title = StringUtil::CleanWebDecode( $_GET['title'] );
   renderSinglePage( $title, $cleanedPage, $url );
}
elseif ( isset( $_GET['perform'] ) && $_GET['perform'] == "getrss" ) {
   // Find SiteSyndication feed and build up a page
   $url = StringUtil::CleanWebDecode( $_GET['page'] );
   $siteSyndication = new SiteSyndication( $url );
   $rssDataAry = $siteSyndication->GetSiteIndexItems();
   if ( is_null( $rssDataAry ) ) {
      $url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8', true );
      print "<h1>Site syndication feed not found</h1><p>URL: $url";
      exit();
   }
   renderHomepage( $rssDataAry );
}
else {
   // If no arguments, build up a page from a default url
   $siteSyndication = new SiteSyndication( DEFAULT_BASE_URL );
   $rssDataAry = $siteSyndication->GetSiteIndexItems();
   renderHomepage( $rssDataAry );
}
exit();

/********************************* End Main *******************************/

/***************************************************************************
 * Function: clean_html_page( html )
 * Accepts: the page to be cleaned
 * Returns: cleaned text
 *
 * Description:
 * Strip all the garbage out of a webpage and make it as plain-text as
 * possible.
 ***************************************************************************/
function clean_html_page( $html )
{
   // Skip everything before first <h1
   $cleaned_page = substr( $html, stripos( $html, '<h1' ) );

   // Skip everything after last "comment", "discuss" or "recommend" whichever is higher up the page
   $comment_position = strripos( $cleaned_page, 'comment' );
   $discuss_position = strripos( $cleaned_page, 'discuss' );
   $recommend_position = strripos( $cleaned_page, 'recommend' );
   if ( $comment_position <= $discuss_position )
      $end_at = $comment_position;
   else
      $end_at = $discuss_position;
   if ( $recommend_position > $end_at )
      $end_at = $recommend_position;
   if ( $end_at !== false )
      $cleaned_page = substr( $cleaned_page, 0, $end_at );

   // Strip all tags not specified
   $cleaned_page = strip_tags( $cleaned_page, "<title><head><h1><h2><h3><h4><h5><h6><p><script><style><cite><strong><blockquote><address><b><i><u><em>" );

   // Now clean up the remaining tags
   $pattern = array(
      '@<title[^>]*?>.*?</title>@sim', // title tags
      '@<head[^>]*?>.*?</head>@sim', //head tags
      '@<noscript[^>]*?>.*?</noscript>@sim', // noscript tags
      '@<script[^>]*?>.*?</script>@sim', // script tags
      '@<span[^>]*?>.*?</span>@sim', // span tags
      '@<style[^>]*?>.*?</style>@siU', // Strip style tags
      '@<![\s\S]*?--[ \t\n\r]*>@' // Strip multi-line comments including CDATA
   );
   $cleaned_page = preg_replace( $pattern, '', $cleaned_page );

   // Clean the attributes from the remaining tags
   $pattern = array(
      '@<*? class=".*?"*?>@', // strip inline class info
      '@<*? style=".*?"*?>@', // strip inline style info
      '@<*? id=".*?"*?>@' // strip id
   );
   $cleaned_page = preg_replace( $pattern, '${1}>', $cleaned_page );

   // Strip whitespace
   $cleaned_page = preg_replace( '/\s+/', ' ', $cleaned_page );

   return $cleaned_page;
} // end function clean_html_page()

/***************************************************************************
 * Function: print_fixed_links( str )
 * Accepts: encoded url of the original page
 * Returns: nothing
 *
 * Description:
 * Prints links for viewing the page with the old version of the program and
 * also a link to the original, uncleaned version of the page.
 ***************************************************************************/
function print_fixed_links( $encoded_link )
{
   // TODO: Once this is working remove the link to the old cleaner
   print "<p><a target=\"_new\" href=\"" . HOST_DOMAIN . "/rss2avantgo.php?perform=getpage&page=$encoded_link\">View with previous version of program</a> | <a target=\"_new\" href=\"" . StringUtil::CleanWebDecode( $encoded_link ) . "\"\">View Original</a></p>";

   return;
} // end print_fixed_links()

/***************************************************************************
 * Function: renderHomepage( SiteIndexItem[] )
 * Accepts: array of SiteIndexItem objects
 * Returns: nothing
 *
 * Description:
 * Render a template-based homepage from provided SiteIndexItems.
***************************************************************************/
function renderHomepage( $siteIndexItemArray )
{
   if ( strlen( $siteIndexItemArray[0]->title ) > 0 )
      $title = $siteIndexItemArray[0]->title;
   else
      $title = "RSS Feed Title Unknown";

   if ( strlen( $siteIndexItemArray[0]->description ) > 0 )
      $description = $siteIndexItemArray[0]->description;
   else
      $description = "RSS Feed Description Unknown";

   $date = date( 'M j, Y' );

   if ( empty( $siteIndexItemArray ) ) {
      // Set a default value if there are no items (content)
      $siteIndexItemArray = array();
      $siteIndexItemArray[0] = new SiteIndexItem( 'No Content', '', '' );
   }
   else {
       // Skip the first element of the array (link back to site)
       unset( $siteIndexItemArray[0] );

       // remove any blank entries
       $siteIndexItemArray = array_filter( $siteIndexItemArray, function( $x ) {
           return !empty( $x->title ); } );
   }

   // Setup template
   $template = new Template();
   $template->AddValue( 'tpl_Title', $title );
   $template->AddValue( 'tpl_Description', $description );
   $template->AddValue( 'tpl_Date', $date );
   $template->AddValueByRef( 'tpl_SiteIndexItemArray', $siteIndexItemArray );

   // Display template
   print $template->Process( './Homepage.tpl' );

   return;
} // end function renderHomepage()

/***************************************************************************
 * Function: renderSinglePage( str, html, url )
 * Accepts: page title, html body to print, original url (not encoded)
 * Returns: nothing
 *
 * Description:
 * Render a template-based page from provided data
 ***************************************************************************/
function renderSinglePage( $title, $html, $url )
{
   if ( empty( $title ) )
      $title = "Unknown page title";

   // Setup template
   $template = new Template();
   $template->AddValue( 'tpl_OriginalUrl', $url );
   $template->AddValue( 'tpl_Title', $title );
   $template->AddValue( 'tpl_PageContent', $html );

   // Display template
   print $template->Process( './SinglePage.tpl' );

   return;
} // end function renderSingl
?>
