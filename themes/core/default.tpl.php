<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?=$title?></title>
<link rel="stylesheet" href="<?=$stylesheet?>">
</head>
<body>
<div id="header">
<div id='login-menu'>
<?=login_menu();?>
</div>
<p class='site-title'><?=$header?></p>
<div id='wrap-main'>
<div id='main' role='main'>
<?=get_messages_from_session()?>
<?=@$main?>
<?=render_views()?>
</div>
</div>
<div id='footer'>
<?=$footer?>
<?=get_debug()?>
</div>
</body>
</html>
