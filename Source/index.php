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

define( "HOST_DOMAIN", 'http://' . $_SERVER['SERVER_NAME'] );
define( "TARGET_RSS_FEED", "http://news.google.com/?output=rss" );

require_once( './SiteSyndication.inc' );
require_once( './SiteIndexItem.inc' );
require_once( './StringUtil.inc' );
require_once( './UrlUtil.inc' );

if ( isset( $_GET['perform'] ) && $_GET['perform'] == "getpage" ) {
   $url = StringUtil::CleanWebDecode( $_GET['page'] );
   $html_page = UrlUtil::GetUrlContents( $url );
   $cleaned_page = clean_html_page( $html_page );
   $additional_pages = find_additional_pages( $html_page );
   print_single_page( $cleaned_page, $_GET['title'], $additional_pages );
} elseif ( isset( $_GET['perform'] ) && $_GET['perform'] == "getrss" ) {
   $url = StringUtil::CleanWebDecode( $_GET['page'] );
   $siteSyndication = new SiteSyndication( $url );
   $rss_data_ary = $siteSyndication->getSiteIndexItems();
   if ( $rss_data_ary == NULL ) {
      $url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8', true );
      print "<h1>RSS not found</h1><p>URL: $url";
      exit();
   }
   print_homepage( $rss_data_ary );
} else {
   $rss_feed = UrlUtil::GetUrlContents( TARGET_RSS_FEED );
   $rss_data_ary = SiteSyndication::parseRssFeed( $rss_feed );
   print_homepage( $rss_data_ary );
}
exit();

/********************************* End Main *******************************/

// any uncommented functions should be considered expiramental

function find_additional_pages( $html )
{
   $url = '';

   $domObj = new domDocument();

   @$domObj->loadHTML( $html );
   $domObj->preserveWhiteSpace = false;

   $anchors = $domObj->getElementsByTagName( 'a' );

   foreach ( $anchors as $anchor ) {
      //$url = "Additional content was found finish implementing next page";
      if ( stripos( $anchor->nodeValue, 'next' ) !== false ) {
         $value = $anchor->getAttribute( 'href' );
         $valueToo = $anchor->nodeValue;
         $url = "Next Page Detected as: <a href=\"$value\">$valueToo</a>";
      }
   }

   return $url;
} // end find_additional_pages()

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
 * Function: print_css()
 * Accepts: nothing
 * Returns: nothing
 *
 * Description:
 * Prints text/css
 ***************************************************************************/
function print_css()
{
   print "<style type='text/css'>\n";
   print "</style>\n";

   return;
} // end print_css()

/***************************************************************************
 * Function: print_footer()
 * Accepts: nothing
 * Returns: nothing
 *
 * Description:
 * Prints text/html suitable for use as page footer
 ***************************************************************************/
function print_footer()
{
   print "\n";

   return;
} // end print_footer()

/***************************************************************************
 * Function: print_homepage( ary[][] )
 * Accepts: array of SiteIndexItem objects
 * Returns: nothing
 *
 * Description:
 * Prints data in object array as a simple homepage.
 ***************************************************************************/
function print_homepage( $siteIndexItemArray )
{
   // Assign page title
   if ( strlen( $siteIndexItemArray[0]->title ) > 0 )
      $feed_title = $siteIndexItemArray[0]->title;
   else
      $feed_title = "RSS Feed Title Unknown";

   // Assign page description
   if ( strlen( $siteIndexItemArray[0]->description ) > 0 )
      $feed_description = $siteIndexItemArray[0]->description;
   else
      $feed_description = "RSS Feed Description Unknown";


   print "<html>\n";
   print "<head>\n";
   print "  <title>$feed_title</title>\n";
   print "  <meta name=\"description\" content=\"$feed_description\">\n";
   print "  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
   print_css();
   print "</head>\n";
   print "<body>\n";

   print_url_form();

   print "<h1>$feed_title</h1>\n";
   print "<p>$feed_description</p>\n";
   print "<p style=\"font-style: italic;\">Date: " . date( "M j, Y" ) . "</p>\n";
   print "<hr>\n";

   // Show the contents of the page
   // Note: start at index 1 because the first two entries are links back to the feeds homepage and whatnot
   for ( $i = 1; $i < sizeof( $siteIndexItemArray ); $i++ ) {
      if ( empty( $siteIndexItemArray[ $i ]->title ) )
         continue;
      print "<h2>" . $siteIndexItemArray[ $i ]->title . "</h2>\n";
      print "<p>" . $siteIndexItemArray[ $i ]->description;
      if ( strlen( $siteIndexItemArray[ $i ]->url ) > 0 ) {
         print " <a href=\"" . HOST_DOMAIN . $_SERVER['PHP_SELF'] . "?perform=getpage&title=" . StringUtil::CleanWebEncode( $siteIndexItemArray[ $i ]->title ) . "&page=" . $siteIndexItemArray[ $i ]->url . "\">Full Story.</a>";
      }
      print "</p>\n";
   }

   print_footer();
   print "</body>\n";
   print "</html>\n";

   return;
} // end function print_homepage()

/***************************************************************************
 * Function: print_single_page( str, str, str )
 * Accepts: html body to print, base64 urlencoded page title, links to
 * additional pages
 * Returns: nothing
 *
 * Description:
 * Prints data passed in as a simple web page.
 ***************************************************************************/
function print_single_page( $html, $title, $additional_pages )
{
   $title = StringUtil::CleanWebDecode( $title );

   print "<html>\n";
   print "<head>\n";
   print "  <title>$title</title>\n";
   print "  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
   print_css();
   print "</head>\n";
   print "<body>\n";

   print_url_form();
   print_fixed_links( $_GET['page'] );

   print $html;
   print $additional_pages;

   print_footer();
   print "</body>\n";
   print "</html>\n";

   return;
} // end function print_single_page()

/***************************************************************************
 * Function: print_url_form()
 * Accepts: nothing
 * Returns: nothing
 *
 * Description:
 * Prints web form to allow submissions of other urls to this script
 ***************************************************************************/
function print_url_form()
{
   print "<script type=\"text/javascript\">\n";
   print "  function doSubmit() {\n";
   print "    document.loadcustom.page.value = window.btoa( encodeURI( document.loadcustom.page.value ) );\n";
   print "    return true;\n";
   print "  }\n";
   print "</script>\n";

   print "<form name=\"loadcustom\" method=\"GET\" action=\"" . HOST_DOMAIN . $_SERVER['PHP_SELF'] . "\" onsubmit=\"doSubmit();\">\n";
   print "  <input type=\"text\" name=\"page\" />\n";
#   print "  <input type=\"hidden\" name=\"perform\" value=\"getpage\" />\n";
   print "  <input type=\"hidden\" name=\"perform\" value=\"getrss\" />\n";
   print "  <input type=\"hidden\" name=\"title\" value=\"Dynamic Load\" />\n";
   print "  <input type=\"submit\" value=\"submit\">\n";
   print "</form>\n";

   return;
} // end print_url_form()
?>
