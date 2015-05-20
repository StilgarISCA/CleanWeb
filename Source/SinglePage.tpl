<html>
<head>
   <title><?=$tpl_Title ?></title>
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
   <script src="PageRequest.js"></script>
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">
   <link rel="stylesheet" href="css/normalize.css">
   <link rel="stylesheet" href="css/skeleton.css">
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
         <p><a target="_new" href="<?=$tpl_OriginalUrl ?>">View Original</a></p>
      </div>
   </div>
   <div class="row">
      <div class="sixteen columns">
         <?=$tpl_PageContent ?>
      </div>
   </div>
</div>
</body>
</html>
