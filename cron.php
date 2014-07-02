<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru
// Использование: cd <рабочий каталог> & php -f cron.php
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
<meta http-equiv="Refresh" content="60;" />
<title><?php echo $config->sitename; ?></title>
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
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
	echo "<h1>" . $config->sitename . "</h1>";
?>
    <div class="panel panel-default">
      <div class="panel-body">
        <p>cron.php должен быть установлен для запуска задачи по-расписанию. Он выполняет задачи парсинга страниц и загрузки картинок в соответствии с очередью ссылок.</p>
        <p>cron.php также можно открыть в браузере - его страница будет обновляться, запуская задачи парсинга сайта.</p>
        <p>Использование: cd &lt;рабочий каталог&gt; &amp; php -f cron.php </p>
      </div>
    </div>
	<iframe src="FlipClock-master/examples/localization.html" width="720" align="middle" scrolling="no"></iframe>
<?php
	$app->info();		
?>
<p><a href="index.php" target="_blank" class="btn btn-primary btn-lg" role="button">Learn more</a></p>
</div>
</div>
<?php
	$app->page_curl_cron();		
	$app->image_curl_cron();		
?>
</body>
</html>