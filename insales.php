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
require_once dirname(__FILE__) . '/bower_components/PHPExcel/Classes/PHPExcel.php';

require_once( dirname(__FILE__) . '/defines.php' );
require_once( dirname(__FILE__) . '/functions.php' );
require_once( dirname(__FILE__) . '/configuration.php' );
require_once( dirname(__FILE__) . '/database.php' );
require_once( dirname(__FILE__) . '/factory.php' );

class InSales {
	private $config;
	private $db;
	
	public function __construct() {
		$this->config = JFactory::getConfig();
		$this->db = JFactory::getDbo();
	}
	
	protected static $instance;
	public static function getInstance()
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
			self::$instance = new InSales;
		}
		return self::$instance;
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
	public function install(){
		$start = microtime(true);
		set_time_limit(0);
		$token = $_REQUEST['token'];
		$secret_key = $this->config->insales_secret_key;
		$password = md5($token . $secret_key);
		$this->db->connect();
		$queries = array();
		$queries[] = 'REPLACE ' . $this->config->dbprefix . TABLE_SETTINGS . '(' . NAME . ',' . VALUE . ') VALUES ("insales_token","' . safe($token) . '")';
		$queries[] = 'REPLACE ' . $this->config->dbprefix . TABLE_SETTINGS . '(' . NAME . ',' . VALUE . ') VALUES ("insales_password","' . safe($password) . '")';
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
		foreach(array("insales_token","insales_password") as $name) $queries[] = 'DELETE FROM ' . $this->config->dbprefix . TABLE_SETTINGS . ' WHERE ' . NAME . '="' . $name . '"';
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	/*
		Получение списка категорий
		Зарос: GET /admin/collections.xml
	*/
	public function collection(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();

		$my_insales_domain = $this->config->my_insales_domain;
		$api_key = $this->config->insales_api_key;
		$password = $this->config->insales_password;
		$root_collection_id = $this->config->insales_root_collection_id?$this->config->insales_root_collection_id:0;
		
		$insales_api = insales_api_client($my_insales_domain, $api_key, $password);
	
		try
		{
			$collections = $insales_api('GET','/admin/collections.json');
			$query = 'REPLACE ' . $this->config->dbprefix . TABLE_INSALES_COLLECTION . '(' . implode('_',array(COLLECTION,ID)) . ',' . implode('_',array(COLLECTION,TITLE)) . ') VALUES (?,?)';
			foreach($collections as $collection){
				$this->db->execute($query,array($collection[ID],$collection[TITLE]));						
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
	
	/*
		Получение списка товаров
		Возможные параметры запроса:
		collection_id - идентификатор категории на складе
		collection_id - идентификатор категории на сайте
		deleted - получить удаленные товары
		updated_since - время в UTC для получения списка измененных товаров с этого времени
		page, per_page - для листания товаров, за раз можно получить не больше 250 товаров. Чтобы получить все нужно в цикле листать страницы пока не закончатся товары.
		Запрос: GET /admin/products.xml?collection_id=478
	*/
	public function product(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();

		// Инициализация загрузки товаров из InSales
		$this->db->execute('REPLACE ' . $this->config->dbprefix . TABLE_SETTINGS . '(' . NAME . ',' . VALUE . ') VALUES ("insales_next_page","1")');

		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function export(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();
		
		$root_collection_id = $this->config->insales_root_collection_id?$this->config->insales_root_collection_id:0;

		$queries = array();
		$queries[] = 'SELECT DISTINCT ' . $root_collection_id . ' AS ' . implode('_',array(PARENT,ID)) . ',' . CHILD . '.' . implode('_',array(COLLECTION,ID)) . ' AS ' . implode('_',array(CHILD,ID)) . ', Value2 AS ' . TITLE . ' FROM ' . $this->config->dbprefix . TABLE_CSV . ' LEFT JOIN ' . $this->config->dbprefix . TABLE_INSALES_COLLECTION . ' AS ' . CHILD . ' ON Value2=' . CHILD . '.' . implode('_',array(COLLECTION,TITLE)) . ' WHERE (' . CHILD . '.' . implode('_',array(COLLECTION,ID)) . ' IS NULL)'; 
		for($i = 2; $i < 5 ; $i++) $queries[] = 'SELECT DISTINCT ' . PARENT . '.' . implode('_',array(COLLECTION,ID)) . ' AS ' . implode('_',array(PARENT,ID)) . ',' . CHILD . '.' . implode('_',array(COLLECTION,ID)) . ' AS ' . implode('_',array(CHILD,ID)) . ', Value' . ($i + 1) . ' AS ' . TITLE . ' FROM ' . $this->config->dbprefix . TABLE_CSV . ' LEFT JOIN ' . $this->config->dbprefix . TABLE_INSALES_COLLECTION . ' AS ' . PARENT . ' ON Value' . $i . '=' . PARENT . '.' . implode('_',array(COLLECTION,TITLE)) . '  LEFT JOIN ' . $this->config->dbprefix . TABLE_INSALES_COLLECTION . ' AS ' . CHILD . ' ON Value' . ($i + 1) . '=' . CHILD . '.' . implode('_',array(COLLECTION,TITLE)) . ' WHERE (NOT ' . PARENT . '.' . implode('_',array(COLLECTION,ID)) . ' IS NULL) AND (' . CHILD . '.' . implode('_',array(COLLECTION,ID)) . ' IS NULL)'; 
		$result = $this->db->query(implode(' UNION ',$queries));
		$rows = $this->db->fetch_all_rows($result);
		$this->db->free_result($result);
		$columns = array(METHOD,PATH,PARAMS,STARTED,implode('_',array(COLLECTION,TITLE))); 
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_INSALES_COLLECTION_UPLOAD_QUEUE . '('. implode(',',$columns) . ') VALUES ('. implode(',',array_fill(0,count($columns),'?')) . ')';
		foreach($rows as $row) if(($row[implode('_',array(PARENT,ID))])&&(!$row[implode('_',array(CHILD,ID))])){
			$collection = array(implode('_',array(PARENT,ID))=>$row[implode('_',array(PARENT,ID))], TITLE=>$row[TITLE]);
			$method = 'POST';
			$path = '/admin/collections.json';
			$values = array($method,$path,json_encode($collection),time(),$row[TITLE]);
			$this->db->execute($query,$values);
		}
				
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_INSALES_COLLECTION);
		$dictionary = array();	while($row=$this->db->fetch_row($result)) $dictionary[$row[implode('_',array(COLLECTION,TITLE))]] = $row[implode('_',array(COLLECTION,ID))];			
		$this->db->free_result($result);

		$insales_product_template = $this->config->insales_product_template;
		$where = array(); foreach($this->config->insales_product_joins as $key=>$value) $where[] = TABLE_INSALES_PRODUCT . '.' . $key . '=' . TABLE_CSV . '.' .$value;
		$queries = array();
		foreach($this->config->product_join_types as $productjointype)
			$queries[] ='SELECT * FROM ' . $this->config->dbprefix . TABLE_INSALES_PRODUCT . ' AS ' . TABLE_INSALES_PRODUCT . ' ' . $productjointype . ' ' . $this->config->dbprefix . TABLE_CSV . ' AS ' . TABLE_CSV . ' ON ' . implode(' AND ', $where);
		$result = $this->db->query(implode(' UNION ',$queries));
		$rows = $this->db->fetch_all_rows($result);
		$this->db->free_result($result);
		$insaleskeys = array_merge(array_keys($this->config->insales_product_joins),array_values($this->config->insales_product_joins));
		$columns = array(METHOD,PATH,PARAMS,STARTED,ID); for($i = 1; $i <= 6; $i++) $columns[] = implode('',array(IMAGE,$i));
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_INSALES_PRODUCT_UPLOAD_QUEUE . '('. implode(',',$columns) . ',' . implode(',',$insaleskeys) . ') VALUES ('. implode(',',array_fill(0,count($insaleskeys)+count($columns),'?')) . ')';
		foreach($rows as $row){
//		b.	В конфиге должна быть возможность указать, какие поля обновлять для карточки товара (по-умолчанию все отключены, 
//		если например ставлю в true на обновление стоимость, то скрипт обновляет только это поле)
			$source = array_merge_recursive ($row[SOURCE]?object_to_array(json_decode($row[SOURCE])):array(),object_to_array(eval($insales_product_template[$row[ID]?'update':'insert'])));
			foreach($this->config->insales_product_fields as $key=>$productfield) {
				if($productfield[2]&&$row[$productfield[2]]&&!$row[ID]) { // Для новых добавлять
					$row[$key] = $row[$productfield[2]];
					eval('$source["' . implode('"]["', explode('/', $productfield[4])) . '"] = $row[$productfield[2]];');
				}
				else if($productfield[2]&&$row[$productfield[2]]&&($productfield[3]&&$row[ID]||!$row[$key])) { // Для старых изменять если
					$row[$key] = $row[$productfield[2]];
					eval('$source["' . implode('"]["', explode('/', $productfield[1])) . '"] = $row[$productfield[2]];');
				}
				else if($row[$key]) // Иначе оставлять прежним
					eval('$source["' . implode('"]["', explode('/', $productfield[1])) . '"] = $row[$key];');
			}
			eval('$source["is_hidden"] = !($row["' . implode('"]||$row["',array_values($this->config->insales_product_joins)) . '"]);');
			if($this->config->insales_product_fields['collectionId'][3])
				for($i = 5; $i >= 2; $i--) if(isset($dictionary[$row["Value" . $i]])) {
					$row['collectionId'] = $dictionary[$row["Value" . $i]];
					eval('$source["' . implode('"]["', explode('/', $this->config->insales_product_fields['collectionId'])) . '"] = $row["collectionId"];');
					break;
				}
			$method = $row[ID]?'PUT':'POST';
			$path = $row[ID]?'/admin/products/' . $row[ID] . '.json':'/admin/products.json';
			$id = $row[ID]?$row[ID]:0;
			$values = array($method,$path,json_encode($source),time(),$id);
			for($i = 1; $i <= 6; $i++) $values[] = $row[implode('',array(IMAGE,$i))];
			foreach($insaleskeys as $key) $values[]=$row[$key];
			$this->db->execute($query,$values);
		}

		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function clear(){
		$start = microtime(true);
		$this->db->connect();
		// Очистка временных таблиц
		$queries = array();
		foreach(array(
			TABLE_INSALES_COLLECTION_UPLOAD_QUEUE,
			TABLE_INSALES_PRODUCT_UPLOAD_QUEUE,
			TABLE_INSALES_IMAGE_UPLOAD_QUEUE
			) as $table) $queries[] = 'TRUNCATE ' . $this->config->dbprefix . $table;		
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
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
		
		$result = $this->db->query('SELECT MAX(' . ID . ') FROM ' . $this->config->dbprefix . TABLE_TASK_QUEUE);
		$taskId = $this->db->fetch_single($result); $taskId=$taskId?$taskId+1:1;
		$this->db->free_result($result);
		$columns = array(ID,CLAZZ,METHOD);
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_TASK_QUEUE . '(' . implode(',',$columns) . ') VALUES (' . implode(',',array_fill(0,count($columns),'?')) . ')';
		$this->db->execute($query,array($taskId++,INSALES,'clear'));
		$this->db->execute($query,array($taskId++,INSALES,'category'));
		$this->db->execute($query,array($taskId++,INSALES,'product'));
		
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
		foreach(array(
			TABLE_INSALES_COLLECTION_UPLOAD_QUEUE,
			TABLE_INSALES_PRODUCT_UPLOAD_QUEUE,
			TABLE_INSALES_IMAGE_UPLOAD_QUEUE
			) as $table) $queries[]='DELETE FROM ' . $this->config->dbprefix . $table . ' WHERE ' . STARTED . '<' . (time() - $this->config->insalesexpiretime);
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);

		$my_insales_domain = $this->config->my_insales_domain;
		$api_key = $this->config->insales_api_key;
		$password = $this->config->insales_password;
		
		$insales_api = insales_api_client($my_insales_domain, $api_key, $password);
		
		$result = $this->db->query('SELECT ' . VALUE . ' FROM ' . $this->config->dbprefix . TABLE_SETTINGS . ' WHERE ' . NAME . '="insales_next_page"');
		$page = $this->db->num_rows($result)?$this->db->fetch_single($result):0;
		$this->db->free_result($result);
		if($page) try
		{
			$count = $this->config->insalespagecount;
			$per_page = $this->config->insalesperpage;
			for($i = 0; $i < $count; $i++) {
				$products = $insales_api('GET','/admin/products.json?page=' . $page . '&per_page=' . $per_page);
				if(!count($products)) { 
					$page = 0;
					break; 
				}
				$columns = array_merge(array(SOURCE),array_keys($this->config->insales_product_fields));
				$query = 'REPLACE ' . $this->config->dbprefix . TABLE_INSALES_PRODUCT . '(' . implode(',',$columns) . ') VALUES (' . implode(',',array_fill(0,count($columns),'?')) . ')';
				foreach($products as $product){
					$source = object_to_array($product);
					$values = array(json_encode($source)); 
					foreach($this->config->insales_product_fields as $productfield) $values[] = eval('return $source["' . implode('"]["', explode('/', $productfield[1])) . '"];');
					$this->db->execute($query,$values);
				}
				$page++;
			}
			$this->db->execute('REPLACE ' . $this->config->dbprefix . TABLE_SETTINGS . '(' . NAME . ',' . VALUE . ') VALUES ("insales_next_page","' . $page . '")');
		}
		catch (InsalesApiException $e)
		{
			var_dump($e);
		}
		catch (InsalesCurlException $e)
		{
			var_dump($e);
		}	
			
		// обработка очереди картинок
		$count = ($this->config->insalescronlimit);
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_INSALES_IMAGE_UPLOAD_QUEUE . ' ORDER BY ' . STARTED . ' LIMIT ' . $count);
		$rows = $this->db->fetch_all_rows($result);
		$this->db->free_result($result);
		$insaleskeys = array_merge(array_keys($this->config->insales_product_joins),array_values($this->config->insales_product_joins));
		$query = 'DELETE FROM ' . $this->config->dbprefix . TABLE_INSALES_IMAGE_UPLOAD_QUEUE . ' WHERE image=?';
		foreach($rows as $row){
			
			// помечаем сразу как обработанный, чтобы не зависать на одной ошибке
			$values = array($row[IMAGE]);
			$this->db->execute($query,$values);
			
			try
			{
				$result = $insales_api($row[METHOD], $row[PATH] , json_decode($row[PARAMS]));
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

		if($page) return;
		
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_INSALES_PRODUCT_UPLOAD_QUEUE);
		$count = $this->db->fetch_single($result);
		$this->db->free_result($result);
		if(!$count) {
			$result = $this->db->query('SELECT MAX(' . ID . ') FROM ' . $this->config->dbprefix . TABLE_TASK_QUEUE);
			$taskId = $this->db->fetch_single($result); $taskId=$taskId?$taskId+1:1;
			$this->db->free_result($result);
			$columns = array(ID,CLAZZ,METHOD);
			$query = 'REPLACE ' . $this->config->dbprefix . TABLE_TASK_QUEUE . '(' . implode(',',$columns) . ') VALUES (' . implode(',',array_fill(0,count($columns),'?')) . ')';
			$this->db->execute($query,array($taskId++,APPLICATION,'clear_xls'));
			$this->db->execute($query,array($taskId++,APPLICATION,'clear_csv'));
			$this->db->execute($query,array($taskId++,APPLICATION,'import_xls'));
			$this->db->execute($query,array($taskId++,APPLICATION,'update_csv'));
			$this->db->execute($query,array($taskId++,INSALES,'export'));
			return;
		}
		
		// Обработка очереди товаров
		$addr = explode('/', $this->config->imagehost);
		$count = ($this->config->insalescronlimit - count($rows));
		$rows = array(); 
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_INSALES_PRODUCT_UPLOAD_QUEUE . ' WHERE image1<>"" ORDER BY ' . STARTED . ' LIMIT ' . ($count-count($rows)));
		$rows = array_merge($rows,$this->db->fetch_all_rows($result));
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_INSALES_PRODUCT_UPLOAD_QUEUE . ' WHERE image1="" ORDER BY ' . STARTED . ' LIMIT ' . ($count-count($rows)));
		$rows = array_merge($rows,$this->db->fetch_all_rows($result));
		$this->db->free_result($result);
		$insaleskeys = array_merge(array_keys($this->config->insales_product_joins),array_values($this->config->insales_product_joins));
		$columns1 = array(METHOD,PATH,PARAMS,STARTED); $columns1[] = IMAGE;
		$query = 'DELETE FROM ' . $this->config->dbprefix . TABLE_INSALES_PRODUCT_UPLOAD_QUEUE . ' WHERE ' . implode('=? AND ', $insaleskeys) . '=?';
		$query1 = 'REPLACE ' . $this->config->dbprefix . TABLE_INSALES_IMAGE_UPLOAD_QUEUE . '('. implode(',',$columns1) . ') VALUES ('. implode(',',array_fill(0,count($columns1),'?')) . ')';
		foreach($rows as $row){
			
			// помечаем сразу как обработанный, чтобы не зависать на одной ошибке
			$values = array(); foreach($insaleskeys as $key) $values[] = $row[$key];
			$this->db->execute($query,$values);
			
			try
			{
				$result = $insales_api($row[METHOD], $row[PATH] , json_decode($row[PARAMS]));
				$source = object_to_array(json_decode($row[PARAMS]));
				$images = array();
				$productId = $row[ID]?$row[ID]:$result[ID];
				if($row[METHOD]=='PUT') {
					foreach($source['variants'] as $variant) {
						$method = 'PUT';
						$path = '/admin/products/' . $source['id'] . '/variants/' . $variant['id'] . '.json';
						$request = array("variant"=>$variant);
						if($this->config->debug) echo "<pre>$method $path</pre>" . json_encode($request);
						$result = $insales_api($method, $path, $request);
					}
					$images = $insales_api('GET','/admin/products/' . $productId . '/images.json');				
				}
				for($i = 1; $i <= 6; $i++) if($row[implode('',array(IMAGE,$i))]&&!$images[$i-1]) {
					$addr[count($addr) - 1] =  $row[implode('',array(IMAGE,$i))];
					$image = array('src'=>implode('/', $addr));
					$values1 = array('POST','/admin/products/' . $productId . '/images.json',json_encode($image),time());
					$values1[] = $row[implode('',array(IMAGE,$i))];
					$this->db->execute($query1,$values1);
				}
			}
			catch (InsalesApiException $e)
			{
				var_dump($e);
				$this->db->execute($query,$values);
			}
			catch (InsalesCurlException $e)
			{
				var_dump($e);
				$this->db->execute($query,$values);
			}	
		}
		
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_INSALES_PRODUCT_UPLOAD_QUEUE);
		$count = $this->db->fetch_single($result);
		$this->db->free_result($result);
		if(!$count) {
			$result = $this->db->query('SELECT MAX(' . ID . ') FROM ' . $this->config->dbprefix . TABLE_TASK_QUEUE);
			$taskId = $this->db->fetch_single($result); $taskId=$taskId?$taskId+1:1;
			$this->db->free_result($result);
			$columns = array(ID,CLAZZ,METHOD);
			$query = 'REPLACE ' . $this->config->dbprefix . TABLE_TASK_QUEUE . '(' . implode(',',$columns) . ') VALUES (' . implode(',',array_fill(0,count($columns),'?')) . ')';
			$this->db->execute($query,array($taskId++,INSALES,'category'));
			$this->db->execute($query,array($taskId++,INSALES,'product'));
			return;
		}
		
		
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
}