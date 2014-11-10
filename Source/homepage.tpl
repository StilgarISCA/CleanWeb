<html>
<head>
   <title>$feed_title</title>
   <meta name="description" content="$feed_description" />
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
print_url_form();

<h1>$feed_title</h1>
<p>$feed_description</p>
<p style="font-style: italic;">$date</p>
<hr>

<h2>$siteIndexItemArray[ $i ]->title</h2>
<p>$siteIndexItemArray[ $i ]->description
   <a href="HOST_DOMAIN . $_SERVER['PHP_SELF'] . "?perform=getpage&title=" . StringUtil::CleanWebEncode( $siteIndexItemArray[ $i ]->title ) . "&page=" . $siteIndexItemArray[ $i ]->url . ">Full Story.</a>
</p>
</body>
</html>
