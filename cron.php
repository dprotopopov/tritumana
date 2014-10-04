<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru
// Использование: cd <рабочий каталог> & php -f cron.php
require_once( dirname(__FILE__) . '/configuration.php' );
require_once( dirname(__FILE__) . '/application.php' );
require_once( dirname(__FILE__) . '/insales.php' );
require_once( dirname(__FILE__) . '/magento.php' );
require_once( dirname(__FILE__) . '/factory.php' );

	$config = JFactory::getConfig();
	$app = JFactory::getApplication();
	$insales = JFactory::getInSales();
	$magento = JFactory::getMagento();
	
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Description" content="" />
<meta http-equiv="Refresh" content="60;" />
<title><?php echo $config->sitename; ?></title>
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<link href="bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="bower_components/bootstrap/dist/css/bootstrap-theme.min.css" rel="stylesheet">
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
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
        <p>Когда будет <br />
Image queue: <strong>0/….</strong><br />
Page queue: <strong>0/….</strong><br />
Это означает что с сайта всё  загружено – нечего больше обрабатывать</p>
        <p>На загрузку всех карточек товара с сайта требуется 4-6 часов</p>
        <p>Сейчас на загрузку и обработку 100 страниц  тратится порядка 2 минут<br />
          Страницы это не только карточки товаров, но и оглавления разделов и т.д. - все ссылки на загруженной странице добавляются к списку известных ссылок. Заранее понять какая страница чем является можно, но сложно и без всяких гарантий правильности, поскольку этот признак в любой момент может меняться владельцами сайта - поэтому должны быть загружены все страницы с сайта, чтобы загрузить все карточки товаров. Целенаправлено загружать только карточки товаров можно, только если владельцы сайта предоставят такой список ссылок - вам надо договориться с владельцем сайта ;).<br />
          На сайте порядка 5000-7000 различных страниц</p>
        <p>То что на хостинге есть различные  лимиты на процессорное время – этого следовало ожидать</p>
        <p>Ошибки типа <strong>QUERY ERROR:</strong>&nbsp;MySQL server has gone away - не ошибки скриптов программы - это проблемы с хостингом - обращайтесь в техподдержку хостера. Это не критическая ошибка – от того что не удалось  соединиться ничего страшного не происходит – просто повторите попытку.</p>
        <p>Ошибки типа <b>404 Not Found</b> - не ошибки скриптов программы - это проблемы с хостингом - обращайтесь в техподдержку хостера. Это не критическая ошибка – от того что не удалось  соединиться ничего страшного не происходит – просто повторите попытку.</p>
        <p>Не гарантирую правильность страниц и результатов при работе через прокси - правильность зависит от типа прокси (использование кеширования пересылаемых данных и т.д.)  - для домашних сетей это наверняка &quot;правильные&quot; прокси, мобильные сети - наверняка &quot;неправильные&quot; прокси.</p>
      </div>
    </div>
	<iframe src="bower_components/flipclock/examples/localization.html" width="100%" align="middle" scrolling="no"></iframe>
	<iframe src="info.php" width="100%" height="240" align="middle" scrolling="auto"></iframe>
<p><a href="index.php" target="_blank" class="btn btn-primary btn-lg" role="button">Learn more</a></p>
</div>
</div>
<?php
	$app->page_curl_cron();		
	$app->image_curl_cron();		
	if($config->insales_enabled) $insales->cron();		
	if($config->magento_enabled) $magento->cron();		
?>
</body>
</html>