<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru
// Использлвание: cd <рабочий каталог> & php -f cron.php
require_once( dirname(__FILE__) . '/configuration.php' );
require_once( dirname(__FILE__) . '/database.php' );
require_once( dirname(__FILE__) . '/application.php' );
require_once( dirname(__FILE__) . '/functions.php' );
require_once( dirname(__FILE__) . '/defines.php' );

	$config = new JConfig();
	$db = new JDatabase();
	$app = new JApp();
	
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Description" content="" />
<title><?php echo $config->sitename; ?></title>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
<div class="jumbotron">
<?php

	if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];
	
	echo "<h1>" . $config->sitename . "</h1>";
	
	switch ($action){
		case 'rebuild_database':
		case 'page_curl_cron':
		case 'image_curl_cron':
		case 'clear_xls':
		case 'clear_url':
		case 'import_xls':
		case 'import_url':
		case 'export_csv':
		case 'task':
			$app->$action();		
			break;
	}
	
	$app->info();		
?>
<form action="index.php" method="post" target="_self">
<div class="input-group">
    <label class="input-group-addon"><input name="action" type="radio" value="rebuild_database" />rebuild_database</label>
    <label class="input-group-addon"><input name="action" type="radio" value="page_curl_cron" />page_curl_cron</label>
    <label class="input-group-addon"><input name="action" type="radio" value="image_curl_cron"/>image_curl_cron</label>
    <label class="input-group-addon"><input name="action" type="radio" value="clear_xls" />clear_xls</label>
    <label class="input-group-addon"><input name="action" type="radio" value="clear_url" />clear_url</label>
    <label class="input-group-addon"><input name="action" type="radio" value="import_xls" />import_xls</label>
    <label class="input-group-addon"><input name="action" type="radio" value="import_url" />import_url</label>
    <label class="input-group-addon"><input name="action" type="radio" value="export_csv" />export_csv</label>
	<span class="input-group-btn"><input name="submit" type="submit" value="Go!" class="btn" /></span>
</div>
</form>
<br />
<form action="index.php" method="post" target="_self">
    <input name="action" type="hidden" value="task" />
    <input name="submit" type="submit" value="task" class="btn btn-success btn-lg btn-block" />
</form>
</div>
</div>
<p><a href="cron.php" target="_blank" class="btn btn-default btn-block">cron.php</a></p>
<p><a href="index.php" target="_self" class="btn btn-default btn-block">index.php</a></p>

</body>
</html>