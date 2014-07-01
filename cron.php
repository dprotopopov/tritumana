<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Description" content="" />
<meta http-equiv="Refresh" content="60;" />
<title><?php echo $config->sitename; ?></title>
</head>
<body>
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
	$app->page_curl_cron();		
	$app->image_curl_cron();		
?>
</body>
</html>