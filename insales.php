<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru


//API InSales предназначено для доступа к информации магазина из интернет с целью создания собственных приложений, синхронизации с иными складскими и учётными системами и т.п.
//API InSales работает через HTTP протокол с использованием (GET/POST/PUT/DELETE) запросов. Данные при обмене передаются в XML-формате или JSON-формате.
//Большая часть URL-ей для выполнения операций совпадает с URL-ями из бекофиса Инсейлс. Для того чтобы система поняла, что это запрос к API вконец URL надо добавить '.xml', а в заголовке HTTP запроса установить Content-Type: application/xml.
//Для каждой группы объектов: заказов, товаров, категорий и т. д. есть свой URL при помощи которого вы можете управлять соответствующими объектами. Другими словами мы попытались организовать свое API в соответствии с принципами REST, насколько это было возможно.
//Про то как подключиться к API вы можете прочитать в разделе "как интегрироваться с InSales".
//Если возникли вопросы пишите на partners@insales.ru .
//Заготовки под разные языки можно посмотреть здесь https://github.com/InSales .
// PHP библиотека для работы с InsalesApi insales.ru
// https://github.com/insales/insales_php_api
require_once( dirname(__FILE__) . '/insales_php_api-master/insales_api.php' );

/** Include PHPExcel */
require_once dirname(__FILE__) . '/PHPExcel_1.8.0_doc/Classes/PHPExcel.php';

require_once( dirname(__FILE__) . '/defines.php' );
require_once( dirname(__FILE__) . '/functions.php' );
require_once( dirname(__FILE__) . '/configuration.php' );
require_once( dirname(__FILE__) . '/database.php' );

class InSales {
	private $config;
	private $db;
	
	public function __construct() {
		$this->config = new JConfig();
		$this->db = new JDatabase();
	}
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
	public function install(){
		$start = microtime(true);
		set_time_limit(0);
		$token = $_REQUEST['token'];
		$secret_key = $this->config->insales_secret_key;
		$password = md5($token . $secret_key);
		$this->db->connect();
		$queries = array();
		$queries[] = 'REPLACE ' . $this->config->dbprefix . TABLE_SETTINGS . '(' . FIELD_NAME . ',' . FIELD_VALUE . ') VALUES ("insales_token","' . safe($token) . '")';
		$queries[] = 'REPLACE ' . $this->config->dbprefix . TABLE_SETTINGS . '(' . FIELD_NAME . ',' . FIELD_VALUE . ') VALUES ("insales_password","' . safe($password) . '")';
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	public function login(){
		$start = microtime(true);
		set_time_limit(0);
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	public function uninstall(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();
		$queries = array();
		foreach(array("insales_token","insales_password") as $name) $queries[] = 'DELETE FROM ' . $this->config->dbprefix . TABLE_SETTINGS . ' WHERE ' . FIELD_NAME . '="' . $name . '"';
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	public function collection(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();

		$my_insales_domain = $this->config->my_insales_domain;
		$api_key = $this->config->insales_api_key;
		$password = $this->config->insales_password;
		
		$insales_api = insales_api_client($my_insales_domain, $api_key, $password);
	
		/*
		Получение списка категорий
		Зарос: GET /admin/collections.xml
		*/
		try
		{
			$collections = $insales_api('GET', '/admin/collections.json');
			$query = 'REPLACE ' . $this->config->dbprefix . TABLE_COLLECTION . '(' . TABLE_COLLECTION . '_' . FIELD_ID . ',' . TABLE_COLLECTION . '_' . FIELD_TITLE . ') VALUES (?,?)';
			foreach($collections as $collection){
				$this->db->execute($query,array($collection[FIELD_ID],$collection[FIELD_TITLE]));						
			}		
		}
		catch (InsalesApiException $e)
		{
			var_dump($e);
		}
		catch (InsalesCurlException $e)
		{
			var_dump($e);
		}	

		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function product(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();

		$my_insales_domain = $this->config->my_insales_domain;
		$api_key = $this->config->insales_api_key;
		$password = $this->config->insales_password;
		
		$insales_api = insales_api_client($my_insales_domain, $api_key, $password);
	
		/*
		Получение списка товаров
		Возможные параметры запроса:
		category_id - идентификатор категории на складе
		collection_id - идентификатор категории на сайте
		deleted - получить удаленные товары
		updated_since - время в UTC для получения списка измененных товаров с этого времени
		page, per_page - для листания товаров, за раз можно получить не больше 250 товаров. Чтобы получить все нужно в цикле листать страницы пока не закончатся товары.
		Запрос: GET /admin/products.xml?category_id=478
		*/
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_PRODUCT . '(' . FIELD_SOURCE . ',' . implode(',',array_keys($this->config->productfields)) . ') VALUES (' . implode(',',array_fill(0,count($this->config->productfields)+1,'?')) . ')';
		for($page=1;true;$page++) {
			try
			{
				$products = $insales_api('GET', '/admin/products.json?page=' . $page);
				if(!count($products)) break;
				
				foreach($products as $product){
					$source = object_to_array($product);
					$values = array(json_encode($source)); 
					foreach($this->config->productfields as $productfield) $values[] = eval('return $source["' . implode('"]["', explode('/', $productfield[1])) . '"];');
					$this->db->execute($query,$values);
				}
			}
			catch (InsalesApiException $e)
			{
			var_dump($e);
			}
			catch (InsalesCurlException $e)
			{
			var_dump($e);
			}	
		}

		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function export(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();
		
		$producttemplate = $this->config->producttemplate;
		$where = array(); foreach($this->config->productjoins as $key=>$value) $where[] = TABLE_PRODUCT . '.' . $key . '=' . TABLE_CSV . '.' .$value;
		$queries = array();
		foreach($this->config->productjointypes as $productjointype)
			$queries[] ='SELECT * FROM ' . $this->config->dbprefix . TABLE_PRODUCT . ' AS ' . TABLE_PRODUCT . ' ' . $productjointype . ' ' . $this->config->dbprefix . TABLE_CSV . ' AS ' . TABLE_CSV . ' ON ' . implode(' AND ', $where);
		$result = $this->db->query(implode(' UNION ',$queries));
		$rows = array(); while($row=$this->db->fetch_row($result)) $rows[] = $row;
		$this->db->free_result($result);
		$insaleskeys = array_merge(array_keys($this->config->productjoins),array_values($this->config->productjoins));
		$columns = array(FIELD_METHOD,FIELD_PATH,FIELD_PARAMS,FIELD_STARTED,FIELD_ID); for($i = 1; $i <= 6; $i++) $columns[] = 'image' . $i;
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_INSALES . '('. implode(',',$columns) . ',' . implode(',',$insaleskeys) . ') VALUES ('. implode(',',array_fill(0,count($insaleskeys)+count($columns),'?')) . ')';
		foreach($rows as $row){
//		b.	В конфиге должна быть возможность указать, какие поля обновлять для карточки товара (по-умолчанию все отключены, 
//		если например ставлю в true на обновление стоимость, то скрипт обновляет только это поле)
			$source = array_merge_recursive ($row[FIELD_SOURCE]?object_to_array(json_decode($row[FIELD_SOURCE])):array(),object_to_array(eval($producttemplate[$row[FIELD_ID]?'update':'insert'])));
			foreach($this->config->productfields as $key=>$productfield) {
				if($productfield[2]&&$row[$productfield[2]]&&$productfield[3]&&!$row[FIELD_ID]) {
					$row[$key] = $row[$productfield[2]];
					eval('$source["' . implode('"]["', explode('/', $productfield[4])) . '"] = $row[$productfield[2]];');
				}
				else if($productfield[2]&&$row[$productfield[2]]&&$productfield[3]&&$row[FIELD_ID]) {
					$row[$key] = $row[$productfield[2]];
					eval('$source["' . implode('"]["', explode('/', $productfield[1])) . '"] = $row[$productfield[2]];');
				}
				else if($row[$key]&&!$productfield[3])
					eval('$source["' . implode('"]["', explode('/', $productfield[1])) . '"] = $row[$key];');
			}
			eval('$source["is_hidden"] = !($row["' . implode('"]||$row["',array_values($this->config->productjoins)) . '"]);');
			$method = $row[FIELD_ID]?'PUT':'POST';
			$path = $row[FIELD_ID]?'/admin/products/' . $row[FIELD_ID] . '.json':'/admin/products.json';
			$id = $row[FIELD_ID]?$row[FIELD_ID]:0;
			$values = array($method,$path,json_encode($source),time(),$id);
			for($i = 1; $i <= 6; $i++) $values[] = $row['image' . $i];
			foreach($insaleskeys as $insaleskey) $values[]=$row[$insaleskey];
			$this->db->execute($query,$values);
		}

		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	/*
	4.	Сделать скрипт обновления наличия и стоимости товарных позиций в интернет магазине
	5.	Работает через API InSales: https://wiki.insales.ru/wiki/%D0%9A%D0%B0%D0%BA_%D0%B8%D0%BD%D1%82%D0%B5%D0%B3%D1%80%D0%B8%D1%80%D0%BE%D0%B2%D0%B0%D1%82%D1%8C%D1%81%D1%8F_%D1%81_InSales 
	6.	Парсит строки в исходном excel: https://drive.google.com/file/d/0B8ifzBzlIfYqTFVaYlZHdVVkX3c/edit?usp=sharing  
	7.	И с помощью API обновляет следующие товарные позиции по следующей логике: 
	a.	Проверка идёт по названию и по Артиклю (должна быть возможность выбирать в конфиге по какому из этих полей проверять или по обоим)
	b.	В конфиге должна быть возможность указать, какие поля обновлять для карточки товара (по-умолчанию все отключены, если например ставлю в true на обновление стоимость, то скрипт обновляет только это поле)
	c.	Обновление по след логике:
	i.	Товар найден на Insales:
	1.	Обновляем указанные в конфиге поля
	ii.	Товар не найден на InSales
	1.	Заполняем все поля, создаём новую карточку товара. 
	2.	Флаг “Видимость на витрине” = “Скрыт”
	iii.	На витрине магазина на Insales есть товары с флагом “Видимость на витриине” = “Выставлен”, но его нет в исходном excel - в этом случае выставляем флан товару “Скрыт”
	iv.	В Insales есть товар, который с флагом “Скрыт”, но есть в исходном файле - выставить ему флаг “Выставлен”
	*/
	public function task2(){
		$start = microtime(true);
		$this->db->connect();
		
		// Очистка временных таблиц
		$queries = array();
		foreach(array(TABLE_COLLECTION,TABLE_PRODUCT,TABLE_XLS,TABLE_CSV,TABLE_INSALES,TABLE_INSALES_IMAGE) as $table) $queries[] = 'TRUNCATE ' . $this->config->dbprefix . $table;		
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
		
		// Загрузка xls файла
		$type = explode(".", $this->config->xls);
		$ext = strtolower($type[count($type)-1]);
		$tempFile = $this->config->xlstempfilename . getmypid() . '.' . $ext;
		// http://stackoverflow.com/questions/1987579/how-to-remove-warning-messages-in-php
		error_reporting(E_ERROR | E_PARSE);
		unlink($tempFile);	
		
		// Загрузка и сохранение файла на диске
		file_put_contents($tempFile, file_get_contents($this->config->xls));
		
		$inputFileType = PHPExcel_IOFactory::identify($tempFile); 
		$reader = PHPExcel_IOFactory::createReader($inputFileType);
		$excel = $reader->load($tempFile);
		$sheet = $excel->getActiveSheet();
		$outline = array_fill(0,10,0);
		$this->db->connect();
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_XLS . '(' . implode(',',array_keys($this->config->xlsfields)) . ') VALUES (' . implode(',',array_fill(0,count($this->config->xlsfields),'?')) . ')';
		foreach($sheet->getRowIterator() as $rowIterator){
			$row = $rowIterator->getRowIndex();
			$outline[$sheet->getRowDimension($row)->getOutlineLevel()]=$row;
			$values = array(); foreach($this->config->xlsfields as $xlsfield) $values[] = trim(eval($xlsfield[1]));
			$this->db->execute($query,$values);
		}
		// Удаление строк с пустой ценой
		$this->db->execute('DELETE FROM ' . $this->config->dbprefix . TABLE_XLS . ' WHERE Column11=""');
		// http://stackoverflow.com/questions/1987579/how-to-remove-warning-messages-in-php
		error_reporting(E_ERROR | E_PARSE);
		unlink($tempFile);	

		// Обновление записей в таблице
		$queries = array();
		$csvfields = array(); foreach($this->config->csvfields as $csvfield=>$values) if($values[2]) $csvfields[$csvfield]=$values[2];
		$where = array(); foreach($this->config->csvjoins as $key=>$value) $where[] = TABLE_XLS . '.' . $key . '=' . TABLE_URL . '.' .$value;
		$queries[] = 'REPLACE ' . $this->config->dbprefix . TABLE_CSV . '(' . implode(',', array_keys($csvfields)) . ') SELECT ' . implode(',', array_values($csvfields)) . ' FROM ' . $this->config->dbprefix . TABLE_XLS . ' AS ' . TABLE_XLS . ' ' . $this->config->csvjointype . ' ' . $this->config->dbprefix . TABLE_URL . ' AS ' . TABLE_URL . ' ON ' . implode(' AND ', $where);
		$queries[] = 'DELETE FROM ' . $this->config->dbprefix . TABLE_CSV . ' WHERE Value12="0"';
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);

		$my_insales_domain = $this->config->my_insales_domain;
		$api_key = $this->config->insales_api_key;
		$password = $this->config->insales_password;
		
		$insales_api = insales_api_client($my_insales_domain, $api_key, $password);
	
		/*
		Получение списка категорий
		Зарос: GET /admin/collections.xml
		*/
		try
		{
			$collections = $insales_api('GET', '/admin/collections.json');
			$query = 'REPLACE ' . $this->config->dbprefix . TABLE_COLLECTION . '(' . TABLE_COLLECTION . '_' . FIELD_ID . ',' . TABLE_COLLECTION . '_' . FIELD_TITLE . ') VALUES (?,?)';
			foreach($collections as $collection){
				$this->db->execute($query,array($collection[FIELD_ID],$collection[FIELD_TITLE]));						
			}		
		}
		catch (InsalesApiException $e)
		{
			var_dump($e);
		}
		catch (InsalesCurlException $e)
		{
			var_dump($e);
		}	

		/*
		Получение списка товаров
		Возможные параметры запроса:
		category_id - идентификатор категории на складе
		collection_id - идентификатор категории на сайте
		deleted - получить удаленные товары
		updated_since - время в UTC для получения списка измененных товаров с этого времени
		page, per_page - для листания товаров, за раз можно получить не больше 250 товаров. Чтобы получить все нужно в цикле листать страницы пока не закончатся товары.
		Запрос: GET /admin/products.xml?category_id=478
		*/
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_PRODUCT . '(' . FIELD_SOURCE . ',' . implode(',',array_keys($this->config->productfields)) . ') VALUES (' . implode(',',array_fill(0,count($this->config->productfields)+1,'?')) . ')';
		for($page=1;true;$page++) {
			try
			{
				$products = $insales_api('GET', '/admin/products.json?page=' . $page);
				if(!count($products)) break;
				
				foreach($products as $product){
					$source = object_to_array($product);
					$values = array(json_encode($source)); 
					foreach($this->config->productfields as $productfield) $values[] = eval('return $source["' . implode('"]["',explode('/',$productfield[1])) . '"];');
					$this->db->execute($query,$values);
				}
			}
			catch (InsalesApiException $e)
			{
				var_dump($e);
			}
			catch (InsalesCurlException $e)
			{
				var_dump($e);
			}	
		}
		
		$producttemplate = $this->config->producttemplate;
		$where = array(); foreach($this->config->productjoins as $key=>$value) $where[] = TABLE_PRODUCT . '.' . $key . '=' . TABLE_CSV . '.' .$value;
		$queries = array();
		foreach($this->config->productjointypes as $productjointype)
			$queries[] ='SELECT * FROM ' . $this->config->dbprefix . TABLE_PRODUCT . ' AS ' . TABLE_PRODUCT . ' ' . $productjointype . ' ' . $this->config->dbprefix . TABLE_CSV . ' AS ' . TABLE_CSV . ' ON ' . implode(' AND ', $where);
		$result = $this->db->query(implode(' UNION ',$queries));
		$rows = array(); while($row=$this->db->fetch_row($result)) $rows[] = $row;
		$this->db->free_result($result);
		$insaleskeys = array_merge(array_keys($this->config->productjoins),array_values($this->config->productjoins));
		$columns = array(FIELD_METHOD,FIELD_PATH,FIELD_PARAMS,FIELD_STARTED,FIELD_ID); for($i = 1; $i <= 6; $i++) $columns[] = 'image' . $i;
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_INSALES . '('. implode(',',$columns) . ',' . implode(',',$insaleskeys) . ') VALUES ('. implode(',',array_fill(0,count($insaleskeys)+count($columns),'?')) . ')';
		foreach($rows as $row){
//		b.	В конфиге должна быть возможность указать, какие поля обновлять для карточки товара (по-умолчанию все отключены, 
//		если например ставлю в true на обновление стоимость, то скрипт обновляет только это поле)
			$source = array_merge_recursive ($row[FIELD_SOURCE]?object_to_array(json_decode($row[FIELD_SOURCE])):array(),object_to_array(eval($producttemplate[$row[FIELD_ID]?'update':'insert'])));
			foreach($this->config->productfields as $key=>$productfield) {
				if($productfield[2]&&$row[$productfield[2]]&&$productfield[3]&&!$row[FIELD_ID]) {
					$row[$key] = $row[$productfield[2]];
					eval('$source["' . implode('"]["', explode('/', $productfield[4])) . '"] = $row[$productfield[2]];');
				}
				else if($productfield[2]&&$row[$productfield[2]]&&$productfield[3]&&$row[FIELD_ID]) {
					$row[$key] = $row[$productfield[2]];
					eval('$source["' . implode('"]["', explode('/', $productfield[1])) . '"] = $row[$productfield[2]];');
				}
				else if($row[$key]&&!$productfield[3])
					eval('$source["' . implode('"]["', explode('/', $productfield[1])) . '"] = $row[$key];');
			}
			eval('$source["is_hidden"] = !($row["' . implode('"]||$row["',array_values($this->config->productjoins)) . '"]);');
			$method = $row[FIELD_ID]?'PUT':'POST';
			$path = $row[FIELD_ID]?'/admin/products/' . $row[FIELD_ID] . '.json':'/admin/products.json';
			$id = $row[FIELD_ID]?$row[FIELD_ID]:0;
			$values = array($method,$path,json_encode($source),time(),$id);
			for($i = 1; $i <= 6; $i++) $values[] = $row['image' . $i];
			foreach($insaleskeys as $insaleskey) $values[]=$row[$insaleskey];
			$this->db->execute($query,$values);
		}

		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function cron(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();
		// Удаляем устаревшие запросы к InSales
		$queries = array();
		foreach(array(TABLE_INSALES,TABLE_INSALES_IMAGE) as $table) $queries[]='DELETE FROM ' . $this->config->dbprefix . $table . ' WHERE ' . FIELD_STARTED . '<' . (time() - $this->config->insalesexpiretime);
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);

		$my_insales_domain = $this->config->my_insales_domain;
		$api_key = $this->config->insales_api_key;
		$password = $this->config->insales_password;
		
		$insales_api = insales_api_client($my_insales_domain, $api_key, $password);

		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_INSALES_IMAGE . ' ORDER BY ' . FIELD_STARTED . ' LIMIT ' . $this->config->insalescronlimit);
		$rows = array(); while($row=$this->db->fetch_row($result)) $rows[] = $row;
		$this->db->free_result($result);
		$insaleskeys = array_merge(array_keys($this->config->productjoins),array_values($this->config->productjoins));
		$query = 'DELETE FROM ' . $this->config->dbprefix . TABLE_INSALES_IMAGE . ' WHERE image=?';
		foreach($rows as $row){
			try
			{
				$result = $insales_api($row[FIELD_METHOD], $row[FIELD_PATH] , json_decode($row[FIELD_PARAMS]));
				$values = array($row['image']);
				// Удаляем обработанные запросы к InSales
				$this->db->execute($query, $values);
			}
			catch (InsalesApiException $e)
			{
				var_dump($e);
			}
			catch (InsalesCurlException $e)
			{
				var_dump($e);
			}	
		}
		
		$addr = explode('/', $this->config->imagehost);
		$count = ($this->config->insalescronlimit-count($rows));
		$rows = array(); 
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_INSALES . ' WHERE image1<>"" ORDER BY ' . FIELD_STARTED . ' LIMIT ' . ($count-count($rows)));
		while($row=$this->db->fetch_row($result)) $rows[] = $row;
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_INSALES . ' WHERE image1="" ORDER BY ' . FIELD_STARTED . ' LIMIT ' . ($count-count($rows)));
		while($row=$this->db->fetch_row($result)) $rows[] = $row;
		$this->db->free_result($result);
		$insaleskeys = array_merge(array_keys($this->config->productjoins),array_values($this->config->productjoins));
		$columns1 = array(FIELD_METHOD,FIELD_PATH,FIELD_PARAMS,FIELD_STARTED); $columns1[] = 'image';
		$query = 'DELETE FROM ' . $this->config->dbprefix . TABLE_INSALES . ' WHERE ' . implode('=? AND ', $insaleskeys) . '=?';
		$query1 = 'REPLACE ' . $this->config->dbprefix . TABLE_INSALES_IMAGE . '('. implode(',',$columns1) . ') VALUES ('. implode(',',array_fill(0,count($columns1),'?')) . ')';
		foreach($rows as $row){
			try
			{
				$result = $insales_api($row[FIELD_METHOD], $row[FIELD_PATH] , json_decode($row[FIELD_PARAMS]));
				$values = array(); foreach($insaleskeys as $insaleskey) $values[] = $row[$insaleskey];
				// Удаляем обработанные запросы к InSales
				$this->db->execute($query, $values);
				if($row[FIELD_METHOD]=='POST') for($i = 1; $i <= 6; $i++) if($row['image' . $i]) {
					$addr[count($addr) - 1] =  $row["image" . $i];
					$image = array('src'=>implode('/', $addr));
					$values1 = array('POST', '/admin/products/' . $result[FIELD_ID] . '/images.json',json_encode($image),time());
					$values1[] = $row['image' . $i];
					$this->db->execute($query1,$values1);
				}
				if($row[FIELD_ID]) { $images = $insales_api('GET', '/admin/products/' . $row[FIELD_ID] . '/images.json');
					for($i = 1; $i <= 6; $i++) if($row['image' . $i]&&!$images[$i-1]) {
						$addr[count($addr) - 1] =  $row["image" . $i];
						$image = array('src'=>implode('/', $addr));
						$values1 = array('POST', '/admin/products/' . $row[FIELD_ID] . '/images.json',json_encode($image),time());
						$values1[] = $row['image' . $i];
						$this->db->execute($query1,$values1);
					}
				}
			}
			catch (InsalesApiException $e)
			{
				var_dump($e);
			}
			catch (InsalesCurlException $e)
			{
				var_dump($e);
			}	
		}
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
}