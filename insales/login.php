﻿<!DOCTYPE html PUBLIC "-/W3C/DTD XHTML 1.0 Transitional/EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>

<script src="bower_components/jquery/dist/jquery.min.js"></script>
<link href="bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="bower_components/bootstrap/dist/css/bootstrap-theme.min.css" rel="stylesheet">
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../bower_components/cssParentSelector/jQuery.cssParentSelector.js"></script>
<link href="../bower_components/slabText/css/slabtext.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../bower_components/slabText/js/jquery.slabtext.js"></script>

	<script src="../bower_components/jquery.Element/jquery.backgroundImageElement.js"></script>
	<script src="../bower_components/jquery.Element/jquery.centerElementContent.js"></script>
	<script src="../bower_components/jquery.Element/jquery.fullScreenElement.js"></script>
	<script src="../bower_components/jquery.Element/jquery.topMenuElement.js"></script>
	<script src="../bower_components/jquery.Element/jquery.rowElementChildren.js"></script>
	<script src="../bower_components/jquery.Element/jquery.windowBorderElement.js"></script>
	<script src="../bower_components/jquery.Element/jquery.browserDetect.js"></script>
	<script src="../bower_components/jquery.Element/slabtexted.js"></script>
    
<link href="../bower_components/jquery.Element/jquery.centerElementContent.css" rel="stylesheet" type="text/css" />
<link href="../bower_components/jquery.Element/jquery.windowBorderElement.css" rel="stylesheet" type="text/css" />
<link href="../bower_components/jquery.Element/jquery.rowElementChildren.css" rel="stylesheet" type="text/css" />
<link href="../bower_components/jquery.Element/jquery.backgroundImageElement.css" rel="stylesheet" type="text/css" />

</head>
<body style="margin:0;">
<div class="full-screen-element">
<img src="../bower_components/jquery.Element/images/2.jpg" class="background-image-element" role="fixed"/>
</div>
<div class="full-screen-element">Page2</div>
<style>#aaa>div{width:70%;height:70%;}</style>
<div id="aaa" class="full-screen-element center-element-content">
<div class="slabtext" style="height:50%;display:block;">Разработчик Дмитрий Протопопов http://protopopov.ru</div>
<div class="slabtext" style="height:50%;display:block;">RBA DESIGN INTERNATIONAL LLC http://rbadesign.us</div>
<img src="../bower_components/jquery.Element/images/3.jpg" class="background-image-element" role="scroll"/>
</div>
<img src="../bower_components/jquery.Element/images/1.jpg" class="background-image-element" />
</body>
</html>
<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru
	/*
	Процедура установки

	После того как пользователь нажал кнопку установить приложение InSales генерирует token (одноразовая строка, хранить у себя не нужно) и устанавливает пароль для подключения password = MD5(token + secret_key), где seсret_key - секретный ключ приложения. Этот пароль нужно сохранить на своей стороне и использовать в дальнейшем при запросах. Пароль для каждого магазина свой.
	После этого отправляется GET запрос на URL для установки приложений с параметрами token , shop и insales_id, где shop - адрес магазина на домене myinsales.ru, например myshop.myinsales.ru , insales_id - внутренний уникальный иденитификатор магазина, который не меняется и по нему однозначно идентифицируется магазин.
	Если приложение отвечает 200 OK, то InSales считает, что приложение успешно установлено. Теперь можно слать запросы к InSales через API используя сгенерированный пароль и идентификатор приложения в качестве логина.
	Поле того как приложение ответило 200 OK, приложение может установить свои обработчики для событий, создать в InSales необходимые данные или загрузить данные в приложение из InSales.
	Особое внимание надо обратить на то, что до тех пор пока InSales не получили ответ 200 OK в ответ на запрос установки приложение считается не установленным, и оно не может совершать запросы к InSales через API.
	
	Рассмотрим на конкретном примере:
	
	1. Вы создали приложение со следующими настройками:
	
	идентификатор приложения - insales_applogin
	секретный ключ приложения - mysecret
	урл установки - http://myapp.ru/install
	
	2. Пользователь магазина test.myinsales.ru (insales_id = 123) нажимает на установку вашего приложения, генерируется случайный token (для примера token123) и идет запрос:
	
	http://myapp.ru/install?shop=test.myinsales.ru&token=token123&insales_id=123
	
	При обработке этого запроса у себя вам нужно по токену вычислить пароль и записать его в своей базе для этого магазина, чтобы в дальнейшем слать запросы по API к нему. В данном случае получается такой пароль:
	
	password = MD5(token + secret_key) = MD5("token123mysecret") = 4c3f4c197336eb97475506e71c839c71
	
	3. Теперь можно послать запрос по API в магазин:
	
	http://insales_applogin:4c3f4c197336eb97475506e71c839c71@test.myinsales.ru/admin/account.xml
	*/

require_once( dirname(__FILE__) . '/../insales.php' );
require_once( dirname(__FILE__) . '/../factory.php' );

	// http://stackoverflow.com/questions/486181/php-suppress-output-within-a-function
	ob_start();

	$insales = JFactory::getInSales();
	$insales->login();
			
	// http://stackoverflow.com/questions/486181/php-suppress-output-within-a-function
	ob_end_clean();
?>
