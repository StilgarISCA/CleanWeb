<html>
<head>
   <title><?=$tpl_Title ?></title>
   <meta name="description" content="<?=$tpl_Description ?>" />
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<!--print_url_form();-->

<h1><?=$tpl_Title ?></h1>
<p><?=$tpl_Description ?></p>
<p style="font-style: italic;"><?=$tpl_Date ?></p>
<hr>
<!--
<h2>$siteIndexItemArray[ $i ]->title</h2>
<p>$siteIndexItemArray[ $i ]->description
   <a href="HOST_DOMAIN . $_SERVER['PHP_SELF'] . "?perform=getpage&title=" . StringUtil::CleanWebEncode( $siteIndexItemArray[ $i ]->title ) . "&page=" . $siteIndexItemArray[ $i ]->url . ">Full Story.</a>
</p>
-->
</body>
</html>
