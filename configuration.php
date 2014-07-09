<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru

class JConfig {
	public $sitename = 'ТУРИСТИЧЕСКОЕ СНАРЯЖЕНИЕ'; // Название загружаемого сайта
	public $debug = '0'; // Флаг вывода отладочной информации
	// Если надо ускорять, то флаг parallel позволяет запускать задачи парсинга в параллельных процессах,
	// если хостинг поддерживает, но не тестировалось
	// http://stackoverflow.com/questions/12214785/how-to-use-pcntl-fork-with-apache
	// It is not possible to use the function 'pcntl_fork' when PHP is used as Apache module.
	// You can only use pcntl_fork in CGI mode or from command-line.
	// Using this function will result in: 'Fatal error: Call to undefined function: pcntl_fork()'
	// The pcntl_fork() function creates a child process that differs from the parent process only in its PID and PPID.
	// Please see your system's fork(2) man page for specific details as to how fork works on your system.
	public $parallel = '0'; // Флаг использования параллельных процессов
	public $imagecronlimit = 100; // Количество загружаемых изображений при одном вызове cron
	public $pagecronlimit = 100; // Количество загружаемых страниц при одном вызове cron
	// Периодичность обновления информации в базе данных
	// время в секундах, когда информация о странице на сайте в базе считается устаревшей и подлежит новой загрузке
	// сейчас указано 10 часов = 36000 секунд
	public $pageupdatetime = 36000; 
	public $dbtype = 'mysqli'; // Не реализовано
	public $host = 'mysql.hostinger.ru'; // Сервер базы данных
	public $user = 'u437594118_tri'; // Логин базы данных
	public $password = 'q1w2e3r4t5y6'; // Пароль базы данных
	public $db = 'u437594118_tri';  // Название базы данных
	public $dbprefix = 'tursportopt_';  // Префикс таблиц в базе данных
	public $persistent = 1;  // Повторно использовать коннект к базе данных
	public $url = 'http://tursportopt.ru';	 // Адрес загружаемого сайта
	public $xls = 'http://www.tursportopt.ru/price/opt.xls'; // Адрес загружаемой таблицы Excel
	public $csv = 'opt.csv'; // Название сохраняемого файла CSV
	public $imagedir = 'images/'; // Директория для сохранения загруженных изображений
	public $imagetempfilename = 'image' ; // Префикс имени временных загруженных файлов изображений
	public $xlstempfilename = 'opt'; // Префикс имени временных загруженных файлов Excel
	public $csvtempfilename = 'opt'; // Префикс имени временных загруженных файлов CSV
	public $urlfields = array( // Настройки парсинга полей страниц сайта
		'productID'=>array('varchar(100)','//div[@class="cpt_maincontent"]//input[@name="productID"]/text()','/.*/i','$0'),
		'product_price'=>array('varchar(50)','//div[@class="cpt_maincontent"]//div[@class="cpt_product_price"]//text()','/\D*(\d*)\D*/i','$1'),
		'product_list_price'=>array('varchar(50)','//div[@class="cpt_maincontent"]//input[@name="product_list_price"]/text()','/.*/i','$0'),
		'product_name'=>array('varchar(100)','//div[@class="cpt_product_name"]//h1/text()','/.*/i','$0'),
		'description'=>array('text','//div[@class="description"]//text()','/.*/i','$0'),
		'manufacture'=>array('varchar(50)','//div[@class="cpt_product_params_selectable"]//td[contains(.,"Производитель:")]/following-sibling::td/text()','/.*/i','$0'),
		'type'=>array('varchar(50)','//div[@class="cpt_product_params_selectable"]//td[contains(.,"Тип:")]/following-sibling::td/text()','/.*/i','$0'),
		'category1'=>array('varchar(100)','(//div[@class="nav"]//a)[1]/text()','/.*/i','$0'),
		'category2'=>array('varchar(100)','(//div[@class="nav"]//a)[2]/text()','/.*/i','$0'),
		'category3'=>array('varchar(100)','(//div[@class="nav"]//a)[3]/text()','/.*/i','$0'),
		'category4'=>array('varchar(100)','(//div[@class="nav"]//a)[4]/text()','/.*/i','$0'),
		'image1'=>array('varchar(100)','(//div[@class="cpt_product_images" or @class="small_img"]//img/../@img_picture|//div[@class="cpt_product_images" or @class="small_img"]//img/../@href[contains(.,"picture")]|//div[@class="cpt_product_images" or @class="small_img"]//img/@src)[1]','/.*/i','$0'),
		'image2'=>array('varchar(100)','(//div[@class="cpt_product_images" or @class="small_img"]//img/../@img_picture|//div[@class="cpt_product_images" or @class="small_img"]//img/../@href[contains(.,"picture")]|//div[@class="cpt_product_images" or @class="small_img"]//img/@src)[2]','/.*/i','$0'),
		'image3'=>array('varchar(100)','(//div[@class="cpt_product_images" or @class="small_img"]//img/../@img_picture|//div[@class="cpt_product_images" or @class="small_img"]//img/../@href[contains(.,"picture")]|//div[@class="cpt_product_images" or @class="small_img"]//img/@src)[3]','/.*/i','$0'),
		'image4'=>array('varchar(100)','(//div[@class="cpt_product_images" or @class="small_img"]//img/../@img_picture|//div[@class="cpt_product_images" or @class="small_img"]//img/../@href[contains(.,"picture")]|//div[@class="cpt_product_images" or @class="small_img"]//img/@src)[4]','/.*/i','$0'),
		'image5'=>array('varchar(100)','(//div[@class="cpt_product_images" or @class="small_img"]//img/../@img_picture|//div[@class="cpt_product_images" or @class="small_img"]//img/../@href[contains(.,"picture")]|//div[@class="cpt_product_images" or @class="small_img"]//img/@src)[5]','/.*/i','$0'),
		'image6'=>array('varchar(100)','(//div[@class="cpt_product_images" or @class="small_img"]//img/../@img_picture|//div[@class="cpt_product_images" or @class="small_img"]//img/../@href[contains(.,"picture")]|//div[@class="cpt_product_images" or @class="small_img"]//img/@src)[6]','/.*/i','$0'),
		'translit'=>array('varchar(100)','//div[@class="cpt_maincontent"]//form[@action]//@action','/(([^\/]*)\/)*$/i','$2'),
		'basePrice'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Баз. опт:")]/following-sibling::td/b/text()','/\D*(\d+).*/i','$1'),
		'price1'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Опт1:")]/following-sibling::td/b/text()','/\D*(\d+).*/i','$1'),
		'price2'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Опт2:")]/following-sibling::td/b/text()','/\D*(\d+).*/i','$1'),
		'price3'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Опт3:")]/following-sibling::td/b/text()','/\D*(\d+).*/i','$1'),
		'weight'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Вес:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'waterProof'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Водонепроницаемость:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'size'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Все размеры:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'garanty'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Гарантия:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'material'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Материал:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'height'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Высота:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'frame'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Каркас:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'supplied'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Комплект поставки:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'materialInternal'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Материал внутренний:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'materialBottom'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Материал пола:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'materialExternal'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Материал внешний:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'volume'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Объем:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'model'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Модель:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'comment'=>array('varchar(100)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Особенности:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'test'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Тест:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'sizeInPackage'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Размер в упаковке:")]/following-sibling::td/b/text()','/.*/i','$0'),
		'packageWeight'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"упаковка вес")]/following-sibling::td/text()','/.*/i','$0'),
		'packageMaterial'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"упаковка материал")]/following-sibling::td/text()','/.*/i','$0'),
		'packageSize'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"упаковка габариты")]/following-sibling::td/text()','/.*/i','$0'),
		'seam'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Обработка швов:")]/following-sibling::td/text()','/.*/i','$0'),
		'color'=>array('varchar(50)','//div[@class="cpt_product_params_fixed"]//td[contains(.,"Цветовое исполнение:")]/following-sibling::td/text()','/.*/i','$0'),
		'store'=>array('varchar(50)','//div[@class="sku"]//span[contains(.,"На складе:")]/../text()','/.*/i','$0'),
	);
	public $xlsfields = array( // Настройки парсинга колонок таблицы Excel
		'Outline1'=>array('varchar(100)','return $sheet->getCellByColumnAndRow(2,$outline[1])->getValue();'),
		'Outline2'=>array('varchar(100)','return $sheet->getCellByColumnAndRow(2,$outline[2])->getValue();'),
		'Outline3'=>array('varchar(100)','return $sheet->getCellByColumnAndRow(2,$outline[3])->getValue();'),
		'Outline4'=>array('varchar(100)','return $sheet->getCellByColumnAndRow(2,$outline[4])->getValue();'),
		'Column1'=>array('varchar(50)','return $sheet->getCellByColumnAndRow(1,$row)->getValue();'),
		'Column2'=>array('varchar(100)','return $sheet->getCellByColumnAndRow(2,$row)->getValue();'),
		'Column3'=>array('varchar(50)','return $sheet->getCellByColumnAndRow(3,$row)->getValue();'),
		'Column4'=>array('varchar(50)','return $sheet->getCellByColumnAndRow(4,$row)->getValue();'),
		'Column5'=>array('varchar(50)','return $sheet->getCellByColumnAndRow(5,$row)->getValue();'),
		'Column6'=>array('varchar(50)','return $sheet->getCellByColumnAndRow(6,$row)->getValue();'),
		'Column7'=>array('varchar(50)','return $sheet->getCellByColumnAndRow(7,$row)->getValue();'),
		'Column8'=>array('varchar(50)','return $sheet->getCellByColumnAndRow(8,$row)->getValue();'),
		'Column9'=>array('varchar(50)','return $sheet->getCellByColumnAndRow(9,$row)->getValue();'),
		'Column10'=>array('varchar(50)','return $sheet->getCellByColumnAndRow(10,$row)->getValue();'),
		'Column11'=>array('varchar(50)','return $sheet->getCellByColumnAndRow(11,$row)->getValue();'),
		'Column12'=>array('varchar(50)','return $sheet->getCellByColumnAndRow(12,$row)->getValue();'),
		'Column13'=>array('varchar(50)','return $sheet->getCellByColumnAndRow(13,$row)->getValue();'),
	);
	// Вид сопоставления записей из таблицы и с сайта
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
	public $jointype = 'LEFT JOIN'; 
	public $joins = array( // Перечень полей для сопоставления записей из таблицы и с сайта
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
	public $csvfields = array( // Список выгружаемых полей
		'Артикул'=>'Column1',
		'Категория'=>'Outline1',
		'Подкатегория 1'=>'Outline2',
		'Подкатегория 2'=>'Outline3',
		'Подкатегория 3'=>'Outline4',
		'Производитель'=>'manufacture',
		'Тип'=>'type',
		'Цвет'=>'color',
		'Размер'=>'size',
		'Вес'=>'weight',
		'Наименование'=>'Outline3',
		'Цена продажи'=>'Column11',
		'Старая цена'=>'',
		'Остаток'=>'',
		'Цена закупки'=>'',
		'Изображение 1'=>'image1',
		'Изображение 2'=>'image2',
		'Изображение 3'=>'image3',
		'Изображение 4'=>'image4',
		'Изображение 5'=>'image5',
		'Изображение 6'=>'image6',
		'Все размеры'=>'size',
		'Материал'=>'material',
		'Особенности'=>'comment',
		'Упаковка вес'=>'packageWeight',
		'Упаковка материал'=>'packageMaterial',
		'Упаковка габариты'=>'packageSize',
		'Объём'=>'volume',
		'Полное описание'=>'description',
		'Краткое описание'=>'',
		'Категория на складе'=>'store',
		'Категория на сайте'=>'',
		'Видимость на витрине'=>'',
		'SEO Категория для канонического url'=>'',
		'SEO тег title'=>'',
		'SEO мета-тег keywords'=>'',
		'SEO-мета тег description'=>'',
		'Внешний код'=>'',
		'Едница измерения'=>'Column12',
		'Штрихкод EAN13'=>'',
		'Штрихкод'=>'',
		'Минимальная цена'=>'',
		'Страна'=>'',
		'Поставщик'=>'',
		'НДС'=>'',
	);
	public $watermark = 'logo_gif2.gif';// Файл с водяным знаком
}