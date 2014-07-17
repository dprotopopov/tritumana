﻿<?php
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
	
	идентификатор приложения - myapplogin
	секретный ключ приложения - mysecret
	урл установки - http://myapp.ru/install
	
	2. Пользователь магазина test.myinsales.ru (insales_id = 123) нажимает на установку вашего приложения, генерируется случайный token (для примера token123) и идет запрос:
	
	http://myapp.ru/install?shop=test.myinsales.ru&token=token123&insales_id=123
	
	При обработке этого запроса у себя вам нужно по токену вычислить пароль и записать его в своей базе для этого магазина, чтобы в дальнейшем слать запросы по API к нему. В данном случае получается такой пароль:
	
	password = MD5(token + secret_key) = MD5("token123mysecret") = 4c3f4c197336eb97475506e71c839c71
	
	3. Теперь можно послать запрос по API в магазин:
	
	http://myapplogin:4c3f4c197336eb97475506e71c839c71@test.myinsales.ru/admin/account.xml
	*/

require_once( dirname(__FILE__) . '/insales.php' );

	// http://stackoverflow.com/questions/486181/php-suppress-output-within-a-function
	ob_start();

	$insales = new InSales();
	$insales->uninstall();
			
	// http://stackoverflow.com/questions/486181/php-suppress-output-within-a-function
	ob_end_clean();
?>