﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
			$app->$action();		
			break;
	}

	$addr = explode('/', "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	$addr[count($addr) - 1] =  'index.php';

?>
    <div class="panel panel-info">
      <div class="panel-body">
        <p>Эквиваленты кнопок:<br />
          	<a href="index.php?action=rebuild_database"><?php echo implode('/',$addr); ?>?action=rebuild_database</a> <br />
          	<a href="index.php?action=page_curl_cron"><?php echo implode('/',$addr); ?>?action=page_curl_cron</a> <br />
       	  <a href="index.php?action=image_curl_cron"><?php echo implode('/',$addr); ?>?action=image_curl_cron</a> <br />
          	<a href="index.php?action=clear_xls"><?php echo implode('/',$addr); ?>?action=clear_xls</a> <br />
          	<a href="index.php?action=clear_url"><?php echo implode('/',$addr); ?>?action=clear_url</a> <br />
          	<a href="index.php?action=import_xls"><?php echo implode('/',$addr); ?>?action=import_xls</a> <br />
          	<a href="index.php?action=import_url"><?php echo implode('/',$addr); ?>?action=import_url</a> <br />
   	    <a href="index.php?action=export_csv"><?php echo implode('/',$addr); ?>?action=export_csv</a></p>
        <p>Если изменяете состав полей или  настройки базы данных – после этого выполните rebuild_database через  index.php – таблицы в базе будут удалены и созданы снова.<br />
          А дальше cron.php опять заполнит таблицы данными</p>
        <p>Для получения результата задачи откройте task.php – это страница загружает xls и выводит результат в поток.</p>
        <p>Если хотите работать с фиксированным файлом <?php echo $config->csv; ?> – то его формированию должны предшествовать clear_xls+import_xls+export_csv</p>
        <p>Если надо ускорять, то флаг parallel позволяет запускать задачи парсинга в параллельных процессах (из командной стороки), если хостинг поддерживает</p>
        <p>Периодичность обновления информации в базе данных - сейчас указано <?php echo $config->pageupdatetime; ?> секунд</p>
        <p>На директорию рекомендую установить пароль</p>
</div>
    </div>
	<iframe src="info.php" width="100%" height="240" align="middle" scrolling="no"></iframe>
    
<form action="index.php" method="post" target="_self">
<div class="input-group">
    <label class="btn btn-default input-group-addon"><input name="action" type="radio" value="rebuild_database" />rebuild_database</label>
    <label class="btn btn-default input-group-addon"><input name="action" type="radio" value="page_curl_cron" />page_curl_cron</label>
    <label class="btn btn-default input-group-addon"><input name="action" type="radio" value="image_curl_cron"/>image_curl_cron</label>
    <label class="btn btn-default input-group-addon"><input name="action" type="radio" value="clear_xls" />clear_xls</label>
    <label class="btn btn-default input-group-addon"><input name="action" type="radio" value="clear_url" />clear_url</label>
    <label class="btn btn-default input-group-addon"><input name="action" type="radio" value="import_xls" />import_xls</label>
    <label class="btn btn-default input-group-addon"><input name="action" type="radio" value="import_url" />import_url</label>
    <label class="btn btn-default input-group-addon"><input name="action" type="radio" value="export_csv" />export_csv</label>
	<span class="input-group-btn"><input name="submit" type="submit" value="Go!" class="btn" /></span>
</div>
</form>
<br />
<p><a href="task.php" target="_blank" class="btn btn-success btn-lg btn-block">task.php</a></p>
</div>
</div>
<p><a href="cron.php" target="_blank" class="btn btn-default btn-block">cron.php</a></p>
<p><a href="index.php" target="_self" class="btn btn-default btn-block">index.php</a></p>
<p><a href="<?php echo $config->csv; ?>" target="_blank" class="btn btn-default btn-block"><?php echo $config->csv; ?> (сперва выполните  clear_xls+import_xls+export_csv)</a></p>
<p><a href="<?php echo $config->xls; ?>" target="_blank" class="btn btn-default btn-block"><?php echo $config->xls; ?></a></p>

</body>
</html>