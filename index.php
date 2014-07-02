<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Description" content="" />
<title><?php echo $config->sitename; ?></title>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

<!-- Optional theme -->
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">

<!-- Latest compiled and minified JavaScript -->
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script></head>
<body>
<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru

require_once( dirname(__FILE__) . '/configuration.php' );
require_once( dirname(__FILE__) . '/database.php' );
require_once( dirname(__FILE__) . '/application.php' );

if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];
	
	$config = new JConfig();
	$db = new JDatabase();
	$app = new JApp();
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
    <input name="action" type="hidden" value="rebuild_database" />
    <input name="submit" type="submit" value="rebuild_database" class="btn btn-danger btn-xs btn-block" />
</form>

<form action="index.php" method="post" target="_self">
    <input name="action" type="hidden" value="page_curl_cron" />
    <input name="submit" type="submit" value="page_curl_cron" class="btn btn-warning btn-xs btn-block"/>
</form>

<form action="index.php" method="post" target="_self">
    <input name="action" type="hidden" value="image_curl_cron" />
    <input name="submit" type="submit" value="image_curl_cron" class="btn btn-warning btn-xs btn-block"/>
</form>

<form action="index.php" method="post" target="_self">
    <input name="action" type="hidden" value="clear_xls" />
    <input name="submit" type="submit" value="clear_xls" class="btn btn-warning btn-xs btn-block" />
</form>

<form action="index.php" method="post" target="_self">
    <input name="action" type="hidden" value="clear_url" />
    <input name="submit" type="submit" value="clear_url" class="btn btn-warning btn-xs btn-block" />
</form>

<form action="index.php" method="post" target="_self">
    <input name="action" type="hidden" value="import_xls" />
    <input name="submit" type="submit" value="import_xls" class="btn btn-warning btn-xs btn-block" />
</form>

<form action="index.php" method="post" target="_self">
    <input name="action" type="hidden" value="import_url" />
    <input name="submit" type="submit" value="import_url" class="btn btn-warning btn-xs btn-block" />
</form>

<form action="index.php" method="post" target="_self">
    <input name="action" type="hidden" value="export_csv" />
    <input name="submit" type="submit" value="export_csv" class="btn btn-warning btn-xs btn-block" />
</form>

<form action="index.php" method="post" target="_self">
    <input name="action" type="hidden" value="task" />
    <input name="search" type="submit" value="task" class="btn btn-success btn-lg btn-block" />
</form>
<p><a href="cron.php" target="_blank" class="btn btn-default btn-block">cron.php</a></p>
<p><a href="index.php" target="_self" class="btn btn-default btn-block">index.php</a></p>

</body>
</html>