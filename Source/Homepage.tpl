<html>
<head>
   <title><?=$tpl_Title ?></title>
   <meta name="description" content="<?=$tpl_Description ?>" />
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
   <script src="PageRequest.js"></script>
   <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css" />
   <link rel="stylesheet" href="<?=HOST_DOMAIN ?>/cleanweb/css/normalize.css" />
   <link rel="stylesheet" href="<?=HOST_DOMAIN ?>/cleanweb/css/skeleton.css" />
</head>
<body>
<div class="container">
   <form name="loadcustom" method="GET" action="<?=HOST_DOMAIN ?><?=$_SERVER['PHP_SELF'] ?>/" onsubmit="doSubmit();">
      <input type="hidden" name="perform" value="getrss" />
      <input type="hidden" name="title" value="Dynamic Load" />    
      <div class="row">
         <div class="two-thirds column">
            <input type="text" name="page" />        
         </div>
         <div class="one-third column">
            <input type="submit" value="submit">
         </div>
      </div>
   </form>
   <div class="row">
      <div class="sixteen columns">
         <h1><?=$tpl_Title ?></h1>
         <p><?=$tpl_Description ?></p>
         <p style="font-style: italic;"><?=$tpl_Date ?></p>
         <hr>
         <?php foreach ( $tpl_SiteIndexItemArray as $siteItem ): ?>
         <h2><?=$siteItem->title ?></h2>
         <!-- TODO: Move this URL logic out of the view -->
         <p><?=$siteItem->description ?> <a href="<?=HOST_DOMAIN . $_SERVER['PHP_SELF'] ?>?perform=getpage&title=<?=StringUtil::CleanWebEncode( $siteItem->title ) ?>&page=<?=$siteItem->url ?>">Full story.</a></p>
         <?php endforeach; ?>
      </div>
   </div>
</div>
</body>
</html>
