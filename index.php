<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru
// Использлвание: cd <рабочий каталог> & php -f cron.php
require_once( dirname(__FILE__) . '/configuration.php' );
require_once( dirname(__FILE__) . '/application.php' );
require_once( dirname(__FILE__) . '/insales.php' );

	$config = new JConfig();
	$app = new JApp();
	$insales = new InSales();
	
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
		case 'clear_page':
		case 'clear_image':
		case 'clear_insales':
		case 'clear_product':
		case 'clear_collection':
		case 'clear_settings':
		case 'import_xls':
		case 'import_url':
		case 'export_csv':
		case 'update_csv':
			$app->$action();		
			break;
		case 'insales_cron':
		case 'insales_install':
		case 'insales_login':
		case 'insales_uninstall':
		case 'insales_collection':
		case 'insales_product':
		case 'insales_export':
			$split=explode('_',$action);
			$insalesAction = $split[1];
			$insales->$insalesAction();		
			break;
		case 'task1':
			$app->task1();		
			break;
		case 'task2':
			$insales->task2();		
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
          <a href="index.php?action=clear_page"><?php echo implode('/',$addr); ?>?action=clear_page</a> <br />
          <a href="index.php?action=clear_image"><?php echo implode('/',$addr); ?>?action=clear_image</a> <br />
          <a href="index.php?action=clear_insales"><?php echo implode('/',$addr); ?>?action=clear_insales</a> <br />
          <a href="index.php?action=clear_product"><?php echo implode('/',$addr); ?>?action=clear_product</a> <br />
          <a href="index.php?action=clear_collection"><?php echo implode('/',$addr); ?>?action=clear_collection</a> <br />
          <a href="index.php?action=clear_settings"><?php echo implode('/',$addr); ?>?action=clear_settings</a> <br />
          <a href="index.php?action=import_xls"><?php echo implode('/',$addr); ?>?action=import_xls</a> <br />
          <a href="index.php?action=import_url"><?php echo implode('/',$addr); ?>?action=import_url</a> <br />
          <a href="index.php?action=export_csv"><?php echo implode('/',$addr); ?>?action=export_csv</a><br />
          <a href="index.php?action=update_csv"><?php echo implode('/',$addr); ?>?action=update_csv</a><br />
          <a href="index.php?action=insales_cron"><?php echo implode('/',$addr); ?>?action=insales_cron</a><br />
          <a href="index.php?action=insales_install"><?php echo implode('/',$addr); ?>?action=insales_install</a><br />
          <a href="index.php?action=insales_login"><?php echo implode('/',$addr); ?>?action=insales_login</a><br />
          <a href="index.php?action=insales_uninstall"><?php echo implode('/',$addr); ?>?action=insales_uninstall</a><br />
          <a href="index.php?action=insales_collection"><?php echo implode('/',$addr); ?>?action=insales_collection</a><br />
          <a href="index.php?action=insales_product"><?php echo implode('/',$addr); ?>?action=insales_product</a><br />
          <a href="index.php?action=insales_export"><?php echo implode('/',$addr); ?>?action=insales_export</a><br />
          </p>
        <p>Если изменяете состав полей или  настройки базы данных – после этого выполните rebuild_database через  index.php – таблицы в базе будут удалены и созданы снова.<br />
          А дальше cron.php опять заполнит таблицы данными</p>
        <p>Для получения результата задачи откройте task.php – это страница загружает xls и выводит результат в поток.</p>
        <p>Если хотите работать с фиксированным файлом <?php echo $config->csv; ?> – то его формированию должны предшествовать clear_xls+import_xls+export_csv</p>
        <p>Если надо ускорять, то флаг parallel позволяет запускать задачи парсинга в параллельных процессах (из командной стороки), если хостинг поддерживает</p>
        <p>Периодичность обновления информации в базе данных - сейчас указано <?php echo $config->pageupdatetime; ?> секунд</p>
        <p>На директорию рекомендую установить пароль</p>
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
        <p>Ошибки типа 
        <b>404 Not Found</b>
         - не ошибки скриптов программы - это проблемы с хостингом - обращайтесь в техподдержку хостера. Это не критическая ошибка – от того что не удалось  соединиться ничего страшного не происходит – просто повторите попытку.</p>
        <p>Не гарантирую правильность страниц и результатов при работе через прокси - правильность зависит от типа прокси  (использование кеширования пересылаемых данных и т.д.) - для домашних сетей это наверняка &quot;правильные&quot; прокси, мобильные сети - наверняка &quot;неправильные&quot; прокси.</p>
      </div>
    </div>
	<iframe src="info.php" width="100%" height="240" align="middle" scrolling="auto"></iframe>
    
<form action="index.php" method="post" target="_self">
<div class="input-group">
    <label class="input-group-addon">
    	<select name="action" value="page_curl_cron" />
        	<option value="rebuild_database">rebuild_database</option>
        	<option value="page_curl_cron">page_curl_cron</option>
        	<option value="image_curl_cron">image_curl_cron</option>
        	<option value="clear_xls">clear_xls</option>
        	<option value="clear_url">clear_url</option>
        	<option value="clear_page">clear_page</option>
        	<option value="clear_image">clear_image</option>
        	<option value="clear_insales">clear_insales</option>
        	<option value="clear_product">clear_product</option>
        	<option value="clear_collection">clear_collection</option>
        	<option value="clear_settings">clear_settings</option>
        	<option value="import_xls">import_xls</option>
        	<option value="import_url">import_url</option>
        	<option value="export_csv">export_csv</option>
        	<option value="update_csv">update_csv</option>
        	<option value="insales_cron">insales_cron</option>
        	<option value="insales_install">insales_install</option>
        	<option value="insales_login">insales_login</option>
        	<option value="insales_uninstall">insales_uninstall</option>
        	<option value="insales_collection">insales_collection</option>
        	<option value="insales_product">insales_product</option>
        	<option value="insales_export">insales_export</option>
        	<option value="task1">task1</option>
        	<option value="task2">task2</option>
        </select>
    </label>
    <label class="input-group-addon">token<input name="token" type="text" /></label>
	<span class="input-group-btn"><input name="submit" type="submit" value="Go!" class="btn" /></span>
</div>
</form>
<br />
<p><a href="task.php" target="_blank" class="btn btn-success btn-lg btn-block">task.php (скачивает xls файл, парсит xls файл, объединяет данные и выдаёт результат в поток)</a></p>
</div>
</div>
<div class="container">
<div class="jumbotron">
<h1>Задача 1</h1>
<p>
1.	Преобразование прайса www.tursportopt.ru/price/opt.xls   в базовый формат каталога товаров
</p>
<p>2.	При формировании файла необходимо парсить по названию товара страницы на сайте поставщика, например: http://tursportopt.ru/category/kovea/  </p>
<p>3.	Скачиваем все картинки, параметры и описание  </p>
<p>4.	Параметры подставляем в соответсвующие столбцы в базовом файле  </p>
<p>5.	Картинки скачиваем на хостиг и добавляем прямую ссылку на файл в базовый excel. Не забываем про водный знак  </p>
<p>6.	Цена продажи = РРЦ (нужно, чтобы столбец можно было настраивать в конфиге)  </p>
<p>7.	Цена закупки = столбец D (нужно, чтобы столбец можно было настраивать в конфиге) </p>
<p><a href="task1.php" target="_blank" class="btn btn-primary btn-lg">task1.php</a></p>
</div>
</div>
<div class="container">
<div class="jumbotron">
<h1>Задача 2</h1>
<p>1.	Попробовать оптимизировать cron.php на хотинге, чтобы он не валил базу при текущих лимитах например уменьшить количество подключений и т.д.
</p>
<p>2.	Настроить генерацию файла на хостинге (task.php) - обвновление два раза в день  </p>
<p>3.	Доработать скрипт (сделать отдельным обработчиком, который можно повесить отдельно на крон или запускать из интерфейс)  </p>
<p>4.	Сделать скрипт обновления наличия и стоимости товарных позиций в интернет магазине  </p>
<p>5.	Работает через API InSales: https://wiki.insales.ru/wiki/%D0%9A%D0%B0%D0%BA_%D0%B8%D0%BD%D1%82%D0%B5%D0%B3%D1%80%D0%B8%D1%80%D0%BE%D0%B2%D0%B0%D1%82%D1%8C%D1%81%D1%8F_%D1%81_InSales  </p>
<p>6.	Парсит строки в исходном excel: https://drive.google.com/file/d/0B8ifzBzlIfYqTFVaYlZHdVVkX3c/edit?usp=sharing  </p>
<p>7.	И с помощью API обновляет следующие товарные позиции по следующей логике: 
  a.	Проверка идёт по названию и по Артиклю (должна быть возможность выбирать в конфиге по какому из этих полей проверять или по обоим)
  b.	В конфиге должна быть возможность указать, какие поля обновлять для карточки товара (по-умолчанию все отключены, если например ставлю в true на обновление стоимость, то скрипт обновляет только это поле)
  c.	Обновление по след логике:
  i.	Товар найден на Insales:
  1.	Обновляем указанные в конфиге поля
  ii.	Товар не найден на InSales
  1.	Заполняем все поля, создаём новую карточку товара. 
  2.	Флаг “Видимость на витрине” = “Скрыт”
  iii.	На витрине магазина на Insales есть товары с флагом “Видимость на витриине” = “Выставлен”, но его нет в исходном excel - в этом случае выставляем флан товару “Скрыт”
  iv.	В Insales есть товар, который с флагом “Скрыт”, но есть в исходном файле - выставить ему флаг “Выставлен”  </p>
<p>8.	Подготовить к запуску по крону на хостинге </p>
<p><a href="task2.php" target="_blank" class="btn btn-primary btn-lg">task2.php</a></p>
</div>
</div>
<p><a href="cron.php" target="_blank" class="btn btn-default btn-block">cron.php (запуск задачи парсинга страниц и загрузки картинок в соответствии с очередью ссылок)</a></p>
<p><a href="index.php" target="_self" class="btn btn-default btn-block">index.php</a></p>
<p><a href="<?php echo $config->csv; ?>" target="_blank" class="btn btn-default btn-block"><?php echo $config->csv; ?> (сперва выполните  clear_xls+import_xls+export_csv)</a></p>
<p><a href="<?php echo $config->xls; ?>" target="_blank" class="btn btn-default btn-block"><?php echo $config->xls; ?></a></p>

</body>
</html>