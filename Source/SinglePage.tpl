<html>
<head>
   <title><?=$tpl_Title ?></title>
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
   <script src="PageRequest.js"></script>
</head>
<body>

<form name="loadcustom" method="GET" action="<?=HOST_DOMAIN ?><?=$_SERVER['PHP_SELF'] ?>\" onsubmit="doSubmit();">
  <input type="text" name="page" />
  <input type="hidden" name="perform" value="getrss" />
  <input type="hidden" name="title" value="Dynamic Load" />
  <input type="submit" value="submit">
</form>

<?=$tpl_PageContent ?>

</body>
</html>
