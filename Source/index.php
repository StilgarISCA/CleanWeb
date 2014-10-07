<?php
/***************************************************************************
Program: cleanweb/index.php
Authors: Glenn Hoeppner
Date Started: 7/17/2013
Date Completed: 

Description:

Parse RSS Feeds and display the list of descriptions/links.  Links clicked
by the user are "cleaned" and sent back to the client.

#TODO:
1.) Display list of RSS links
2.) Add ability to add RSS feeds
3.) Display only relavent content
4.) Consolidate content split over multiple pages
5.) Make google-like homepage
6.) Some sites use relative pathing, so next link is directed toward teh wrong site
7.) Blank pages show up if a page is only an ad or video
8.) Auto-detect urls inside returned codes and make them link properly (might be hard with bit.ly-type links)
10.) Add url button to any page - DONE
11.) Try to prevent injection by sanitizing submit input
12.) auto-append http:// to submitted links - DONE
13.) If there is no RSS Found, just dump the page content
14.) The site is dumping text that isn't inside tags. I think I could get rid of more garbage if there's a way to strip it out.
***************************************************************************/
define("HOST_DOMAIN", "http://www.yakhair.com");
define("TARGET_RSS_FEED", "http://news.google.com/?output=rss");
   if( isset( $_GET['perform'] ) && $_GET['perform'] == "getpage" ) {
      $url = urldecode( base64_decode( $_GET['page'] ) );
      $html_page = get_url_contents( $url );
      $cleaned_page = clean_html_page( $html_page );    
      $additional_pages = find_additional_pages( $html_page );
      print_single_page( $cleaned_page, $_GET['title'], $additional_pages );
   } elseif( isset( $_GET['perform'] ) && $_GET['perform'] == "getrss" ) {
      $url = urldecode( base64_decode( $_GET['page'] ) );
      $siteSyndication = new SiteSyndication( $url );
      if ( $siteSyndication->getFeeds() == '' ) {
         $url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8', true );
         print "<h1>RSS not found</h1><p>URL: $url";
         exit();
      }
      $rss_feed = get_url_contents( $siteSyndication->getFeeds() );
      $rss_data_ary = parse_rss_feed( $rss_feed );
      print_homepage( $rss_data_ary );
   } else {
      $rss_feed = get_url_contents( TARGET_RSS_FEED );
      $rss_data_ary = parse_rss_feed( $rss_feed );
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
Function: clean_html_page( html )
Accepts: the page to be cleaned
Returns: cleaned text

Description:
Strip all the garbage out of a webpage and make it as plain-text as
possible.

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
   $pattern = array (
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
   $pattern = array (
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
Function: get_url_contents( url )
Accepts: url of site to get
Returns: page contents

Description:
Use fopen to make a connection to the url passed into the function, read
the page 4096 bytes at a time and return the contents.
***************************************************************************/
function get_url_contents( $url )
{
   $file_contents = "";
   if( !( $file_handle = @fopen( $url, "r" ) ) )
      die( "Error! Could not open: $url" );
   
   while( !feof( $file_handle ) )
      $file_contents .= fgets( $file_handle, 4096 );
   
   fclose( $file_handle );
   
   return $file_contents;
} // end function get_url_contents()


/***************************************************************************
Function: parse_rss_feed( str )
Accepts: rss data as xml format
Returns: array[n]['title'] = Feed Title
         array[n]['description'] = Feed description (stripped of html)
         array[n]['link'] = Feed link (encoded)

Description:
Parses RSS (v2?) XML for the following: TITLE, DESCRIPTION, LINK, and stores
that information within an array which is then returned.

HTML is stripped from the discription, and the link is encoded using
urlencode and base64.

***************************************************************************/
function parse_rss_feed( $xml_data )
{
   // parse the xml into an array
   $xml_parser = xml_parser_create();
   xml_parser_set_option( $xml_parser, XML_OPTION_SKIP_WHITE, 1 );
   xml_parser_set_option( $xml_parser, XML_OPTION_CASE_FOLDING, 1 );
   xml_parse_into_struct( $xml_parser, $xml_data, $values ); 
   xml_parse( $xml_parser, $xml_data );
   xml_parser_free( $xml_parser );
  
   // loop through array pulling/formatting desired data, and throw into 2d array
   $cur_count = 0;    
   for( $i=0; $i < sizeof( $values ); $i++ ){
      switch(  $values[$i]['tag'] ){
         case "TITLE":
            $data_ary[$cur_count]['title'] = $values[$i]['value'];
            break;
         case "DESCRIPTION":
            // strip out any HTML/javascript garbage contaminating the feeds
            $data_ary[$cur_count]['description'] = strip_tags( $values[$i]['value'] );
            break;             
         case "LINK":
            // encode the url for easy passing through GET later
            $data_ary[$cur_count]['link'] = base64_encode( urlencode($values[$i]['value'] ) );
            break;       
         case "ITEM":
            $cur_count++;
            break;
      }      
   }
       
   return $data_ary;
} // end function parse_rss_feed()

/***************************************************************************
Function: print_fixed_links( str )
Accepts: encoded url of the original page
Returns: nothing

Description:
Prints links for viewing the page with the old version of the program and
also a link to the original, uncleaned version of the page.

***************************************************************************/
function print_fixed_links( $encoded_link )
{  
   // TODO: Once this is working remove the link to the old cleaner
   print "<p><a target=\"_new\" href=\"". HOST_DOMAIN ."/rss2avantgo.php?perform=getpage&page=$encoded_link\">View with previous version of program</a> | <a target=\"_new\" href=\"" . urldecode( base64_decode( $encoded_link) ) . "\"\">View Original</a></p>";
   
   return;
} // end print_fixed_links()

/***************************************************************************
Function: print_css()
Accepts: nothing
Returns: nothing

Description:
Prints text/css

***************************************************************************/
function print_css()
{
   print "<style type='text/css'>\n";
   print "</style>\n";
   
   return;
} // end print_css()

/***************************************************************************
Function: print_footer()
Accepts: nothing
Returns: nothing

Description:
Prints text/html suitable for use as page footer
***************************************************************************/
function print_footer()
{
   print "\n";
   
   return;
} // end print_footer()

/***************************************************************************
Function: print_homepage( ary[][] )
Accepts: array[n]['title'] = Feed Title
         array[n]['description'] = Feed description (stripped of html)
         array[n]['link'] = Feed link (encoded)
Returns: nothing

Description:
Prints data in array as a simple homepage.

***************************************************************************/
function print_homepage( $data_ary )
{

   // Assign page title
   if( strlen( $data_ary[0]['title'] ) > 0 )
      $feed_title = $data_ary[0]['title'];
   else
      $feed_title = "RSS Feed Title Unknown";
      
   // Assign page description
   if( strlen($data_ary[0]['description'] ) > 0 )
      $feed_description = $data_ary[0]['description'];
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
   print "<p style=\"font-style: italic;\">Date: " . date("M j, Y") . "</p>\n";
   print "<hr>\n";
    
   // Show the contents of the page
   // Note: start at index 1 because the first two entries are links back to the feeds homepage and whatnot
   for( $i = 1; $i < sizeof( $data_ary ); $i++ ) {
      if( empty( $data_ary[$i]['title'] ) )
         continue;
      print "<h2>". $data_ary[$i]['title'] ."</h2>\n";
      print "<p>" . $data_ary[$i]['description'];
      if( strlen( $data_ary[$i]['link'] ) > 0 ) {
         print " <a href=\"" . HOST_DOMAIN . $_SERVER['PHP_SELF'] . "?perform=getpage&title=" . base64_encode( urlencode( $data_ary[$i]['title'] ) ) . "&page=" . $data_ary[$i]['link'] . "\">Full Story.</a>";
      }
      print "</p>\n";
   }
   
   print_footer(); 
   print "</body>\n";
   print "</html>\n";

   return;
} // end function print_homepage()

/***************************************************************************
Function: print_single_page( str, str, str )
Accepts: html body to print, base64 urlencoded page title, links to
         additional pages
Returns: nothing

Description:
Prints data passed in as a simple web page.

***************************************************************************/
function print_single_page( $html, $title, $additional_pages )
{
   $title = urldecode(base64_decode( $title ) );

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
Function: print_url_form()
Accepts: nothing
Returns: nothing

Description:
Prints web form to allow submissions of other urls to this script
***************************************************************************/
function print_url_form()
{  
   print "<script type=\"text/javascript\">\n";
   print "  function doSubmit() {\n";
   print "    document.loadcustom.page.value = window.btoa( encodeURI( document.loadcustom.page.value ) );\n";
   print "    return true;\n";
   print "  }\n";
   print "</script>\n";
   
   print "<form name=\"loadcustom\" method=\"GET\" action=\"" . HOST_DOMAIN .  $_SERVER['PHP_SELF'] . "\" onsubmit=\"doSubmit();\">\n";
   print "  <input type=\"text\" name=\"page\" />\n";
#   print "  <input type=\"hidden\" name=\"perform\" value=\"getpage\" />\n";
print "  <input type=\"hidden\" name=\"perform\" value=\"getrss\" />\n";
   print "  <input type=\"hidden\" name=\"title\" value=\"Dynamic Load\" />\n";
   print "  <input type=\"submit\" value=\"submit\">\n";
   print "</form>\n";
   
   return;
} // end print_url_form() 
?>
