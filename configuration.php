<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru
require_once( dirname(__FILE__) . '/defines.php' );

class JConfig {
	public $sitename = 'ТУРИСТИЧЕСКОЕ СНАРЯЖЕНИЕ'; // Название загружаемого сайта-донора
	public $debug = '0'; // Флаг вывода отладочной информации

	// Настройки для cron
	// Если надо ускорять, то флаг parallel позволяет запускать задачи парсинга в параллельных процессах,
	// если хостинг поддерживает, но не тестировалось
	// http://stackoverflow.com/questions/12214785/how-to-use-pcntl-fork-with-apache
	// It is not possible to use the function 'pcntl_fork' when PHP is used as Apache module.
	// You can only use pcntl_fork in CGI mode or from command-line.
	// Using this function will result in: 'Fatal error: Call to undefined function: pcntl_fork()'
	// The pcntl_fork() function creates a child process that differs from the parent process only in its PID and PPID.
	// Please see your system's fork(2) man page for specific details as to how fork works on your system.
	public $parallel = '0'; // Флаг использования параллельных процессов
	public $imagecronlimit = 20; // Количество загружаемых изображений с сайта-донора при одном вызове cron
	public $pagecronlimit = 20; // Количество загружаемых страниц с сайта-донора при одном вызове cron
	public $insalescronlimit = 10; // Количество выгружаемых карточек товара на InSales при одном вызове cron
	
	// Периодичность обновления информации в базе данных
	// время в секундах, когда информация о странице на сайте-доноре в базе считается устаревшей и подлежит новой загрузке
	// сейчас указано 100 часов = 360000 секунд
	public $pageupdatetime = 360000; 
	public $insalesexpiretime = 360000; 
	
//База данных создана. Для пользователя u266351659_tri установлен пароль dSmOfOyH1b
//mysql.hostinger.ru

	// Настройки коннекта к базе данных
//	public $dbtype = 'mysqli'; // Не реализовано
//	public $host = 'mysql.hostinger.ru'; // Сервер базы данных
//	public $user = 'u333079267_tir'; // Логин базы данных
//	public $password = 'q1w2e3r4t5y6'; // Пароль базы данных
//	public $db = 'u333079267_tri';  // Название базы данных
//	public $dbprefix = 'tursportopt_';  // Префикс таблиц в базе данных
//	public $persistent = 1;  // Повторно использовать коннект к базе данных
	
	public $dbtype = 'mysqli'; // Не реализовано
	public $host = 'mysql.hostinger.ru'; // Сервер базы данных
	public $user = 'u266351659_tri'; // Логин базы данных
	public $password = 'dSmOfOyH1b'; // Пароль базы данных
	public $db = 'u266351659_tri';  // Название базы данных
	public $dbprefix = 'tursportopt_';  // Префикс таблиц в базе данных
	public $persistent = 1;  // Повторно использовать коннект к базе данных
	
//	public $dbtype = 'mysqli'; // Не реализовано
//	public $host = 'localhost'; // Сервер базы данных
//	public $user = 'tritumana'; // Логин базы данных
//	public $password = '123456'; // Пароль базы данных
//	public $db = 'tritumana';  // Название базы данных
//	public $dbprefix = 'tursportopt_';  // Префикс таблиц в базе данных
//	public $persistent = 1;  // Повторно использовать коннект к базе данных
	
//	public $dbtype = 'mysqli'; // Не реализовано
//	public $host = 'mysql.hostinger.ru'; // Сервер базы данных
//	public $user = 'u437594118_tri'; // Логин базы данных
//	public $password = 'q1w2e3r4t5y6'; // Пароль базы данных
//	public $db = 'u437594118_tri';  // Название базы данных
//	public $dbprefix = 'tursportopt_';  // Префикс таблиц в базе данных
//	public $persistent = 1;  // Повторно использовать коннект к базе данных
//	

//	public $my_insales_domain = 'tritumana.myinsales.ru';  // Домен в InSales
//	// Access Key для сайта
//	public $insales_api_key = '0dd445ca899ed0c1b37cc2a918a6c225';  // Access Key в InSales
//	public $insales_password = 'f386964f0dac0a91c1aaad96f54b9fdd';  // Access Key в InSales

	public $my_insales_domain = 'aaabbbccc.myinsales.ru';  // Домен в InSales
	// Access Key для сайта
	public $insales_api_key = 'd6db90ca9a04291079db86be4d85d6ba';  // Access Key в InSales
	public $insales_password = 'c4af6e1ea8613a04d19089014ecfaf07';  // Access Key в InSales
	
	public $parent_collection_id = 2933878;  // Корневая коллекция на витрине в InSales

	// Для приложения
	public $myapplogin = '11111111111'; //	идентификатор приложения - myapplogin
	public $insales_secret_key = '71315e8f4d83599d49408533aa9ed8a4';  // Секретный ключ приложения в InSales
	
	// Информация о сайте-доноре
	public $url = 'http://tursportopt.ru';	 // Адрес загружаемого сайта (сайт-донор)
	public $xls = 'http://www.tursportopt.ru/price/opt.xls'; // Адрес загружаемой таблицы Excel с сайта-донора 
	
	// Локальные файлы и дериктории
	public $csv = 'opt.csv'; // Название сохраняемого файла CSV
	
//	public $imagehost = 'http://tritumana.meximas.com/'; // Хост для сохранения загруженных изображений (добавояется в качестве превикса к пути картинки
//	public $imagedir = 'images/'; // Директория для сохранения загруженных изображений
	
	public $imagehost = 'http://aaabbbccc.esy.es/'; // Хост для сохранения загруженных изображений (добавляется в качестве префикса к пути картинки)
	public $imagedir = 'images/'; // Директория для сохранения загруженных изображений
	
	// Временные файлы
	public $imagetempfilename = 'image' ; // Префикс имени временных загруженных файлов изображений
	public $xlstempfilename = 'opt'; // Префикс имени временных загруженных файлов Excel
	public $csvtempfilename = 'opt'; // Префикс имени временных файлов CSV
	
	/*
		Структура записи
		Имя => array(
			SQL data type http://dev.mysql.com/doc/refman/5.1/en/create-table.html
			XPath http://php.net/manual/ru/book.dom.php
			PCRE preg_replace pattern - The pattern to search for. It can be either a string or an array with strings. http://php.net/manual/en/function.preg-replace.php
			PCRE preg_replace replacement - The string or an array with strings to replace. http://php.net/manual/en/function.preg-replace.php
		)
	*/
	public $urlfields = array( // Настройки парсинга полей страниц сайта
		'productID'=>array('VARCHAR(100) DEFAULT ""',
			'//div[@class="cpt_maincontent"]//input[@name="productID"]/text()',
			'/.*/i','$0'),
		'product_price'=>array('DECIMAL(18,2)',
			'//div[@class="cpt_maincontent"]//div[@class="cpt_product_price"]//text()',
			'/\D*(\d*)\D*/i','$1'),
		'product_list_price'=>array('DECIMAL(18,2)',
			'//div[@class="cpt_maincontent"]//input[@name="product_list_price"]/text()',
			'/.*/i','$0'),
		'product_name'=>array('VARCHAR(100) DEFAULT ""',
			'//div[@class="cpt_product_name"]//h1/text()',
			'/.*/i','$0'),
		'description'=>array('TEXT',
			'//div[@class="description"]//text()',
			'/.*/i','$0'),
		'manufacture'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_selectable"]//td[contains(.,"Производитель:")]/following-sibling::td/text()',
			'/.*/i','$0'),
		'type'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_selectable"]//td[contains(.,"Тип:")]/following-sibling::td/text()',
			'/.*/i','$0'),
		'category1'=>array('VARCHAR(100) DEFAULT ""',
			'(//div[@class="nav"]//a)[1]/text()',
			'/.*/i','$0'),
		'category2'=>array('VARCHAR(100) DEFAULT ""',
			'(//div[@class="nav"]//a)[2]/text()',
			'/.*/i','$0'),
		'category3'=>array('VARCHAR(100) DEFAULT ""',
			'(//div[@class="nav"]//a)[3]/text()',
			'/.*/i','$0'),
		'category4'=>array('VARCHAR(100) DEFAULT ""',
			'(//div[@class="nav"]//a)[4]/text()',
			'/.*/i','$0'),
		'image1'=>array('VARCHAR(100) DEFAULT ""',
			'(//div[@class="cpt_product_images" or @class="small_img"]//img/../@img_picture|//div[@class="cpt_product_images" or @class="small_img"]//img/../@href[contains(.,"picture")]|//div[@class="cpt_product_images" or @class="small_img"]//img/@src)[1]',
			'/.*/i','$0'),
		'image2'=>array('VARCHAR(100) DEFAULT ""',
			'(//div[@class="cpt_product_images" or @class="small_img"]//img/../@img_picture|//div[@class="cpt_product_images" or @class="small_img"]//img/../@href[contains(.,"picture")]|//div[@class="cpt_product_images" or @class="small_img"]//img/@src)[2]',
			'/.*/i','$0'),
		'image3'=>array('VARCHAR(100) DEFAULT ""',
			'(//div[@class="cpt_product_images" or @class="small_img"]//img/../@img_picture|//div[@class="cpt_product_images" or @class="small_img"]//img/../@href[contains(.,"picture")]|//div[@class="cpt_product_images" or @class="small_img"]//img/@src)[3]',
			'/.*/i','$0'),
		'image4'=>array('VARCHAR(100) DEFAULT ""',
			'(//div[@class="cpt_product_images" or @class="small_img"]//img/../@img_picture|//div[@class="cpt_product_images" or @class="small_img"]//img/../@href[contains(.,"picture")]|//div[@class="cpt_product_images" or @class="small_img"]//img/@src)[4]',
			'/.*/i','$0'),
		'image5'=>array('VARCHAR(100) DEFAULT ""',
			'(//div[@class="cpt_product_images" or @class="small_img"]//img/../@img_picture|//div[@class="cpt_product_images" or @class="small_img"]//img/../@href[contains(.,"picture")]|//div[@class="cpt_product_images" or @class="small_img"]//img/@src)[5]',
			'/.*/i','$0'),
		'image6'=>array('VARCHAR(100) DEFAULT ""',
			'(//div[@class="cpt_product_images" or @class="small_img"]//img/../@img_picture|//div[@class="cpt_product_images" or @class="small_img"]//img/../@href[contains(.,"picture")]|//div[@class="cpt_product_images" or @class="small_img"]//img/@src)[6]',
			'/.*/i','$0'),
		'translit'=>array('VARCHAR(100) DEFAULT ""',
			'//div[@class="cpt_maincontent"]//form[@action]//@action',
			'/(([^\/]*)\/)*$/i','$2'),
		'basePrice'=>array('DECIMAL(18,2)',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Баз. опт:")]/following-sibling::td/b/text()',
			'/\D*(\d+).*/i','$1'),
		'price1'=>array('DECIMAL(18,2)',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Опт1:")]/following-sibling::td/b/text()',
			'/\D*(\d+).*/i','$1'),
		'price2'=>array('DECIMAL(18,2)',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Опт2:")]/following-sibling::td/b/text()',
			'/\D*(\d+).*/i','$1'),
		'price3'=>array('DECIMAL(18,2)',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Опт3:")]/following-sibling::td/b/text()',
			'/\D*(\d+).*/i','$1'),
		'weight'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Вес:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'waterProof'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Водонепроницаемость:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'size'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Все размеры:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'garanty'=>array('INTEGER',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Гарантия:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'material'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Материал:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'height'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Высота:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'frame'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Каркас:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'supplied'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Комплект поставки:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'materialInternal'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Материал внутренний:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'materialBottom'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Материал пола:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'materialExternal'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Материал внешний:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'volume'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Объем:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'model'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Модель:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'comment'=>array('VARCHAR(100) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Особенности:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'test'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Тест:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'sizeInPackage'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Размер в упаковке:")]/following-sibling::td/b/text()',
			'/.*/i','$0'),
		'packageWeight'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"упаковка вес")]/following-sibling::td/text()',
			'/.*/i','$0'),
		'packageMaterial'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"упаковка материал")]/following-sibling::td/text()',
			'/.*/i','$0'),
		'packageSize'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"упаковка габариты")]/following-sibling::td/text()',
			'/.*/i','$0'),
		'seam'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Обработка швов:")]/following-sibling::td/text()',
			'/.*/i','$0'),
		'color'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="cpt_product_params_fixed"]//td[contains(.,"Цветовое исполнение:")]/following-sibling::td/text()',
			'/.*/i','$0'),
		'store'=>array('VARCHAR(50) DEFAULT ""',
			'//div[@class="sku"]//span[contains(.,"На складе:")]/../text()',
			'/.*/i','$0'),
	);
	/*
		Структура записи
		Имя => array(
			SQL data type http://dev.mysql.com/doc/refman/5.1/en/create-table.html
			PHPExcel eval script
		)
		Предопределённые переменные
			$sheet - лист Excel
			$outline - массив номеров строк в соответствии с иерархическим представлением данных на листе
			$row - номер текущей строки в файле
	*/	
	public $xlsfields = array( // Настройки парсинга колонок таблицы Excel
		'Outline1'=>array('VARCHAR(100) DEFAULT ""','return $sheet->getCellByColumnAndRow(2,$outline[1])->getValue();'),
		'Outline2'=>array('VARCHAR(100) DEFAULT ""','return $sheet->getCellByColumnAndRow(2,$outline[2])->getValue();'),
		'Outline3'=>array('VARCHAR(100) DEFAULT ""','return $sheet->getCellByColumnAndRow(2,$outline[3])->getValue();'),
		'Outline4'=>array('VARCHAR(100) DEFAULT ""','return $sheet->getCellByColumnAndRow(2,$outline[4])->getValue();'),
		'Column1'=>array('VARCHAR(50) DEFAULT ""','return $sheet->getCellByColumnAndRow(1,$row)->getValue();'),
		'Column2'=>array('VARCHAR(100) DEFAULT ""','return $sheet->getCellByColumnAndRow(2,$row)->getValue();'),
		'Column3'=>array('VARCHAR(50) DEFAULT ""','return $sheet->getCellByColumnAndRow(3,$row)->getValue();'),
		'Column4'=>array('VARCHAR(50) DEFAULT ""','return $sheet->getCellByColumnAndRow(4,$row)->getValue();'),
		'Column5'=>array('VARCHAR(50) DEFAULT ""','return $sheet->getCellByColumnAndRow(5,$row)->getValue();'),
		'Column6'=>array('VARCHAR(50) DEFAULT ""','return $sheet->getCellByColumnAndRow(6,$row)->getValue();'),
		'Column7'=>array('VARCHAR(50) DEFAULT ""','return $sheet->getCellByColumnAndRow(7,$row)->getValue();'),
		'Column8'=>array('VARCHAR(50) DEFAULT ""','return $sheet->getCellByColumnAndRow(8,$row)->getValue();'),
		'Column9'=>array('VARCHAR(50) DEFAULT ""','return $sheet->getCellByColumnAndRow(9,$row)->getValue();'),
		'Column10'=>array('VARCHAR(50) DEFAULT ""','return $sheet->getCellByColumnAndRow(10,$row)->getValue();'),
		'Column11'=>array('VARCHAR(50) DEFAULT ""','return $sheet->getCellByColumnAndRow(11,$row)->getValue();'),
		'Column12'=>array('VARCHAR(50) DEFAULT ""','return $sheet->getCellByColumnAndRow(12,$row)->getValue();'),
		'Column13'=>array('VARCHAR(50) DEFAULT ""','return $sheet->getCellByColumnAndRow(13,$row)->getValue();'),
	);
	// Вид сопоставления записей из таблицы и с сайта-донора
	// http://dev.mysql.com/doc/refman/5.0/en/join.html
	// JOIN
	// LEFT JOIN
	// RIGHT JOIN
	// INNER JOIN
	// OUTER JOIN
	// и т.д.
	// Здесь технология проста
	// 1.	Скачиваем в таблицу A все записи из xls файла
	// 2.	Скачиваем в таблицу B все записи из карточек товара с сайта
	// 3.	Объединяем записи
	// A JOIN B ON … - находятся записи где для A существует в В и для B существует в A
	// A LEFT JOIN B ON … - находятся ВСЕ записи A и для B существует в A
	// и т.д. в соответствии с синтаксисом sql
	public $csvjointype = 'LEFT JOIN'; 
	/*
		Структура записи
		Имя колонки XLS файла => Имя карточки товара с сайта-донора
	*/
	public $csvjoins = array( // Перечень полей для сопоставления записей из таблицы и с сайта
		'Outline3'=>'product_name',
	);
	public $urlkeys = array( // Перечень полей первичного ключа
		'productID',
		'product_name',
	);
	public $xlskeys = array( // Перечень полей первичного ключа
		'Column1',
		'Column2',
	);
	public $csvkeys = array( // Перечень полей первичного ключа
		'Value1',
		'Value11',
	);
	/*
		Структура записи
		Имя => array(
			SQL data type http://dev.mysql.com/doc/refman/5.1/en/create-table.html
			Заголовок
			Поле XLS
		)
		Предопределённые переменные
			$sheet - лист Excel
			$outline - массив номеров строк в соответствии с иерархическим представлением данных на листе
			$row - номер текущей строки в файле
	*/	
	public $csvfields = array( // Список выгружаемых полей
		'Value1'=>array('VARCHAR(20) DEFAULT ""','Артикул','Column1'),
		'Value2'=>array('VARCHAR(100) DEFAULT ""','Категория','Outline1'),
		'Value3'=>array('VARCHAR(100) DEFAULT ""','Подкатегория 1','Outline2'),
		'Value4'=>array('VARCHAR(100) DEFAULT ""','Подкатегория 2','Outline3'),
		'Value5'=>array('VARCHAR(100) DEFAULT ""','Подкатегория 3','Outline4'),
		'Value6'=>array('VARCHAR(100) DEFAULT ""','Производитель','manufacture'),
		'Value7'=>array('VARCHAR(50) DEFAULT ""','Тип','type'),
		'Value8'=>array('VARCHAR(50) DEFAULT ""','Цвет','color'),
		'Value9'=>array('VARCHAR(50) DEFAULT ""','Размер','size'),
		'Value10'=>array('VARCHAR(50) DEFAULT ""','Вес','weight'),
		'Value11'=>array('VARCHAR(100) DEFAULT ""','Наименование','Outline3'),
		'Value12'=>array('DECIMAL(18,2)','Цена продажи','Column11'),
		'Value13'=>array('DECIMAL(18,2)','Старая цена',''),
		'Value14'=>array('INTEGER','Остаток',''),
		'Value15'=>array('DECIMAL(18,2)','Цена закупки',''),
		'Value16'=>array('TEXT','Изображение 1','image1'),
		'Value17'=>array('TEXT','Изображение 2','image2'),
		'Value18'=>array('TEXT','Изображение 3','image3'),
		'Value19'=>array('TEXT','Изображение 4','image4'),
		'Value20'=>array('TEXT','Изображение 5','image5'),
		'Value21'=>array('TEXT','Изображение 6','image6'),
		'Value22'=>array('VARCHAR(100) DEFAULT ""','Все размеры','size'),
		'Value23'=>array('VARCHAR(100) DEFAULT ""','Материал','material'),
		'Value24'=>array('TEXT','Особенности','comment'),
		'Value25'=>array('VARCHAR(50) DEFAULT ""','Упаковка вес','packageWeight'),
		'Value26'=>array('VARCHAR(100) DEFAULT ""','Упаковка материал','packageMaterial'),
		'Value27'=>array('VARCHAR(50) DEFAULT ""','Упаковка габариты','packageSize'),
		'Value28'=>array('VARCHAR(50) DEFAULT ""','Объём','volume'),
		'Value29'=>array('TEXT','Полное описание','description'),
		'Value30'=>array('TEXT','Краткое описание',''),
		'Value31'=>array('VARCHAR(100) DEFAULT ""','Категория на складе','store'),
		'Value32'=>array('VARCHAR(100) DEFAULT ""','Категория на сайте',''),
		'Value33'=>array('VARCHAR(100) DEFAULT ""','Видимость на витрине',''),
		'Value34'=>array('TEXT','SEO Категория для канонического url',''),
		'Value35'=>array('TEXT','SEO тег title',''),
		'Value36'=>array('TEXT','SEO мета-тег keywords',''),
		'Value37'=>array('TEXT','SEO-мета тег description',''),
		'Value38'=>array('VARCHAR(100) DEFAULT ""','Внешний код',''),
		'Value39'=>array('VARCHAR(100) DEFAULT ""','Едница измерения','Column12'),
		'Value40'=>array('VARCHAR(100) DEFAULT ""','Штрихкод EAN13',''),
		'Value41'=>array('VARCHAR(100) DEFAULT ""','Штрихкод',''),
		'Value42'=>array('VARCHAR(50) DEFAULT ""','Минимальная цена',''),
		'Value43'=>array('VARCHAR(50) DEFAULT ""','Страна',''),
		'Value44'=>array('VARCHAR(50) DEFAULT ""','Поставщик',''),
		'Value45'=>array('VARCHAR(50) DEFAULT ""','НДС',''),
	);
	
	/*
		a.	Проверка идёт по названию и по Артиклю (должна быть возможность выбирать в конфиге 
		по какому из этих полей проверять или по обоим)
	*/
	public $productjointypes = array('LEFT JOIN','RIGHT JOIN'); 
	/*
		Структура записи
		Имя колонки товара InSales => Имя колонки CSV файла
	*/
	public $productjoins = array( // Перечень полей для сопоставления записей
		'title'=>'Value11',
		'sku'=>'Value1',
	);
	/*
		b.	В конфиге должна быть возможность указать, какие поля обновлять для карточки товара (по-умолчанию все отключены, 
		если например ставлю в true на обновление стоимость, то скрипт обновляет только это поле)
	
		Структура записи
		Имя => array(
			SQL data type http://dev.mysql.com/doc/refman/5.1/en/create-table.html
			select/update json path
			join field
			обновлять для карточки товара
			insert json path
		)
		
			Признак новой записи ($join->id)
			Специальное поле $join->' . TABLE_COLLECTION . '_' . FIELD_ID . '
	*/	
	public $productfields = array( // Настройки парсинга полей xml
		'id'=>array('INTEGER','id','id',true,'id'),
		'collectionId'=>array('INTEGER','collection_id','collection_id',true,'collection_id'),
		'title'=>array('VARCHAR(100) DEFAULT ""','title','Value11',true,'title'),
		'htmlTitle'=>array('VARCHAR(100) DEFAULT ""','html_title','Value11',true,'html_title'),
		'permalink'=>array('VARCHAR(100) DEFAULT ""','permalink','',true,'permalink'),
		'sku'=>array('VARCHAR(20) DEFAULT ""','variants/0/sku','Value1',true,'variants_attributes/0/sku'),
		'variantId'=>array('VARCHAR(50) DEFAULT ""','variants/0/id','',true,'variants_attributes/0/id'),
		'variantTitle'=>array('VARCHAR(50) DEFAULT ""','variants/0/title','Value11',true,'variants_attributes/0/title'),
		'costPrice'=>array('DECIMAL(18,2)','variants/0/cost_price','Value15',true,'variants_attributes/0/cost_price'),
		'quantity'=>array('INTEGER','variants/0/quantity','Value14',true,'variants_attributes/0/quantity'),
		'oldPrice'=>array('DECIMAL(18,2)','variants/0/old_price','Value13',true,'variants_attributes/0/old_price'),
		'price'=>array('DECIMAL(18,2)','variants/0/price','Value12',true,'variants_attributes/0/price'),
		'description'=>array('TEXT','description','Value29',true,'description'),
		'shortDescription'=>array('TEXT','short_description','Value30',true,'short_description'),
		'metaKeywords'=>array('TEXT','meta_keywords','Value36',true,'meta_keywords'),
		'metaDescription'=>array('TEXT','meta_description','Value37',true,'meta_description'),
		/*
		iii.	На витрине магазина на Insales есть товары с флагом “Видимость на витриине” = “Выставлен”, но его нет в исходном excel - в этом случае выставляем флан товару “Скрыт”
		iv.	В Insales есть товар, который с флагом “Скрыт”, но есть в исходном файле - выставить ему флаг “Выставлен”
		*/
		'isHidden'=>array('VARCHAR(50) DEFAULT ""','is_hidden','',true,'is_hidden'),
		'image1'=>array('TEXT','images/0/src','Value16',true,'images/0/src'),
		'image2'=>array('TEXT','images/1/src','Value17',true,'images/1/src'),
		'image3'=>array('TEXT','images/2/src','Value18',true,'images/2/src'),
		'image4'=>array('TEXT','images/3/src','Value19',true,'images/3/src'),
		'image5'=>array('TEXT','images/4/src','Value20',true,'images/4/src'),
		'image6'=>array('TEXT','images/5/src','Value21',true,'images/5/src'),
	);
	/*
	Добавление товара со свойствами
	Запрос: POST /admin/products.xml
	
	Изменение параметров товара
	Важный момент: передавать надо все параметры, если ранее установленный параметр не будет передан в запросе, то он будет удален.
	Запрос: PUT /admin/products/#{id}.xml
	*/
	public $producttemplate = array(
		'insert'=>'return array(
			variants_attributes => array(
				0 => array(),
			),
			images => array(
				0 => array(),
				1 => array(),
				2 => array(),
				3 => array(),
				4 => array(),
				5 => array(),
			),
		);',
		'update'=>'return array(
			images => array(
				0 => array(),
				1 => array(),
				2 => array(),
				3 => array(),
				4 => array(),
				5 => array(),
			),
		);',
	);

	public $productkeys = array( // Перечень полей первичного ключа
		'variantId',
		'sku',
	);
	
	public $watermark = 'logo_gif2.gif';// Файл с водяным знаком
}