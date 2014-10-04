<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru

/** Include PHPExcel */
require_once dirname(__FILE__) . '/bower_components/PHPExcel/Classes/PHPExcel.php';

require_once( dirname(__FILE__) . '/defines.php' );
require_once( dirname(__FILE__) . '/functions.php' );
require_once( dirname(__FILE__) . '/configuration.php' );
require_once( dirname(__FILE__) . '/database.php' );
require_once( dirname(__FILE__) . '/factory.php' );

class Magento {
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
			self::$instance = new Magento;
		}
		return self::$instance;
	}
	/*
		Получение списка категорий
	*/
	public function category(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();

		$apiUrl = $this->config->magento_api_url;
		$apiUser = $this->config->magento_api_user;
		$apiKey = $this->config->magento_api_key;
		$rootCategoryId = $this->config->magento_root_category_id;

		$proxy = new SoapClient($apiUrl);	
		$sessionId = $proxy->login((object)array(
			'username' => $apiUser, 
			'apiKey' => $apiKey
		));			

		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY . '(' . implode('_',array(CATEGORY,ID)) . ',' . implode('_',array(CATEGORY,NAME)) . ') VALUES (?,?)';
		
		try
		{
			$result = $proxy->catalogCategoryTree((object)array(
				'sessionId' => $sessionId->result, 
				'parentId' => $rootCategoryId
			));
			$categories = $result->result;
			while(count($categories)){
				$category = array_pop($categories);
				$this->db->execute($query,array($category[implode('_',array(CATEGORY,ID))],$category[NAME]));	
				if(isset($category[CHILDREN])) $categories = array_merge($categories, $category[CHILDREN]);			
			}		
		}
		catch (Exception $e)
		{
			var_dump($e);
		}

		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	/*
		Получение списка товаров
	*/
	public function product(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();

		$apiUrl = $this->config->magento_api_url;
		$apiUser = $this->config->magento_api_user;
		$apiKey = $this->config->magento_api_key;
				
		$proxy = new SoapClient($apiUrl);	
		$sessionId = $proxy->login((object)array(
			'username' => $apiUser, 
			'apiKey' => $apiKey
		));			

		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT . '(' . SOURCE . ',' . implode(',',array_keys($this->config->magento_product_fields)) . ') VALUES (' . implode(',',array_fill(0,count($this->config->magento_product_fields)+1,'?')) . ')';
		try
		{
			$result = $proxy->catalogProductList((object)array(
				'sessionId' => $sessionId->result, 
				'filters' => null
			));
			$products = $result->result;			
			foreach($products as $product){
				$source = object_to_array($product);
				$values = array(json_encode($source)); 
				foreach($this->config->magento_product_fields as $productfield) $values[] = eval('return $source["' . implode('"]["', explode('/', $productfield[1])) . '"];');
				$this->db->execute($query,$values);
			}
		}
		catch (Exception $e)
		{
			var_dump($e);
		}

		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function export(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();
		
		$rootCategoryId = $this->config->magento_root_category_id||0;

		$queries = array();
		$queries[] = 'SELECT DISTINCT ' . $rootCategoryId . ' AS ' . implode('_',array(PARENT,ID)) . ',' . CHILD . '.' . implode('_',array(CATEGORY,ID)) . ' AS ' . ID . ', Value2 AS ' . NAME . ' FROM ' . $this->config->dbprefix . TABLE_CSV . ' LEFT JOIN ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY . ' AS ' . CHILD . ' ON Value2=' . CHILD . '.' . implode('_',array(CATEGORY,NAME)) . ' WHERE (' . CHILD . '.' . implode('_',array(CATEGORY,ID)) . ' IS NULL)'; 
		for($i = 2; $i < 5 ; $i++) $queries[] = 'SELECT DISTINCT ' . PARENT . '.' . implode('_',array(CATEGORY,ID)) . ' AS ' . implode('_',array(PARENT,ID)) . ',' . CHILD . '.' . implode('_',array(CATEGORY,ID)) . ' AS ' . ID . ', Value' . ($i + 1) . ' AS ' . NAME . ' FROM ' . $this->config->dbprefix . TABLE_CSV . ' LEFT JOIN ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY . ' AS ' . PARENT . ' ON Value' . $i . '=' . PARENT . '.' . implode('_',array(CATEGORY,NAME)) . '  LEFT JOIN ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY . ' AS ' . CHILD . ' ON Value' . ($i + 1) . '=' . CHILD . '.' . implode('_',array(CATEGORY,NAME)) . ' WHERE (NOT ' . PARENT . '.' . implode('_',array(CATEGORY,ID)) . ' IS NULL) AND (' . CHILD . '.' . implode('_',array(CATEGORY,ID)) . ' IS NULL)'; 
		$result = $this->db->query(implode(' UNION ',$queries));
		$rows = $this->db->fetch_all_rows($result);
		$this->db->free_result($result);
		$columns = array(METHOD,implode('_',array(PARENT,ID)),PARAMS,STARTED,implode('_',array(CATEGORY,NAME))); 
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY_QUEUE . '('. implode(',',$columns) . ') VALUES ('. implode(',',array_fill(0,count($columns),'?')) . ')';
		foreach($rows as $row) 
			if(($row[implode('_',array(PARENT,ID))])&&(!$row[ID])){
				$parentId = $row[implode('_',array(PARENT,ID))];
				$category = array(NAME=>$row[NAME]);
				$method = 'catalog_category.create';
				$values = array($method,$parentId,json_encode($category),time(),$row[NAME]);
				$this->db->execute($query,$values);
			}
			else if((!$row[implode('_',array(PARENT,ID))])){
				$parentId = $this->config->magento_root_category_id;
				$category = array(NAME=>$row[NAME]);
				$method = 'catalog_category.create';
				$values = array($method,$parentId,json_encode($category),time(),$row[NAME]);
				$this->db->execute($query,$values);
			}
				
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY);
		$dictionary = array();	while($row=$this->db->fetch_row($result)) $dictionary[$row[implode('_',array(CATEGORY,NAME))]] = $row[implode('_',array(CATEGORY,ID))];			
		$this->db->free_result($result);

		$magento_product_template = $this->config->magento_product_template;
		$where = array(); foreach($this->config->magento_product_joins as $key=>$value) $where[] = TABLE_MAGENTO_PRODUCT . '.' . $key . '=' . TABLE_CSV . '.' .$value;
		$queries = array();
		foreach($this->config->product_join_types as $productjointype)
			$queries[] ='SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT . ' AS ' . TABLE_MAGENTO_PRODUCT . ' ' . $productjointype . ' ' . $this->config->dbprefix . TABLE_CSV . ' AS ' . TABLE_CSV . ' ON ' . implode(' AND ', $where);
		$result = $this->db->query(implode(' UNION ',$queries));
		$rows = $this->db->fetch_all_rows($result);
		$this->db->free_result($result);
		$magentokeys = array_merge(array_keys($this->config->magento_product_joins),array_values($this->config->magento_product_joins));
		$columns = array(METHOD,implode('_',array(PRODUCT,ID)),PARAMS,STARTED); for($i = 1; $i <= 6; $i++) $columns[] = IMAGE . $i;
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_QUEUE . '('. implode(',',$columns) . ',' . implode(',',$magentokeys) . ') VALUES ('. implode(',',array_fill(0,count($magentokeys)+count($columns),'?')) . ')';
		foreach($rows as $row){
//		b.	В конфиге должна быть возможность указать, какие поля обновлять для карточки товара (по-умолчанию все отключены, 
//		если например ставлю в true на обновление стоимость, то скрипт обновляет только это поле)
			$source = array_merge_recursive ($row[SOURCE]?object_to_array(json_decode($row[SOURCE])):array(),object_to_array(eval($magento_product_template[$row[ID]?'update':'insert'])));
			foreach($this->config->magento_product_fields as $key=>$productfield) {
				if($productfield[2]&&$row[$productfield[2]]&&!$row[ID]) { // Для новых добавлять
					$row[$key] = $row[$productfield[2]];
					eval('$source["' . implode('"]["', explode('/', $productfield[4])) . '"] = $row[$productfield[2]];');
				}
				else if($productfield[2]&&$row[$productfield[2]]&&$productfield[3]&&$row[ID]) { // Для старых изменять если
					$row[$key] = $row[$productfield[2]];
					eval('$source["' . implode('"]["', explode('/', $productfield[1])) . '"] = $row[$productfield[2]];');
				}
				else if($row[$key]) // Иначе оставлять прежним
					eval('$source["' . implode('"]["', explode('/', $productfield[1])) . '"] = $row[$key];');
			}
			eval('$source["visibility"] = !($row["' . implode('"]||$row["',array_values($this->config->magento_product_joins)) . '"]);');
			if($this->config->magento_product_fields['collectionId'][3])
				for($i = 5; $i >= 2; $i--) if(isset($dictionary[$row["Value" . $i]])) {
					$row['categoryId'] = $dictionary[$row["Value" . $i]];
					eval('$source["' . implode('"]["', explode('/', $this->config->magento_product_fields['categoryId'])) . '"] = $row["categoryId"];');
					break;
				}
			$method = $row[implode('_',array(PRODUCT,ID))]?'catalog_product.create':'catalog_product.update';
			$productId = $row[implode('_',array(PRODUCT,ID))]?$row[implode('_',array(PRODUCT,ID))]:0;
			$values = array($method,$productId,json_encode($source),time());
			for($i = 1; $i <= 6; $i++) $values[] = $row[IMAGE . $i];
			foreach($magentokeys as $key) $values[]=$row[$key];
			$this->db->execute($query,$values);
		}

		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function task2(){
		$start = microtime(true);
		$this->db->connect();
		
		$apiUrl = $this->config->magento_api_url;
		$apiUser = $this->config->magento_api_user;
		$apiKey = $this->config->magento_api_key;
		$rootCategoryId = $this->config->magento_root_category_id||0;
		
		$proxy = new SoapClient($apiUrl);	
		$sessionId = $proxy->login((object)array(
			'username' => $apiUser, 
			'apiKey' => $apiKey
		));			
	
		// Очистка временных таблиц
		$queries = array();
		foreach(array(
			TABLE_MAGENTO_CATEGORY,
			TABLE_MAGENTO_PRODUCT,
			TABLE_XLS,
			TABLE_CSV,
			TABLE_MAGENTO_PRODUCT_QUEUE,
			TABLE_MAGENTO_IMAGE_QUEUE
			) as $table) $queries[] = 'TRUNCATE ' . $this->config->dbprefix . $table;		
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
		
		// Инициализация загрузки товаров из Magento
		$this->db->execute('DELETE FROM ' . $this->config->dbprefix . TABLE_SETTINGS . ' WHERE ' . NAME . '="magento_circle"');

		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY . '(' . implode('_',array(CATEGORY,ID)) . ',' . implode('_',array(CATEGORY,NAME)) . ') VALUES (?,?)';
		
		try
		{
			$result = $proxy->catalogCategoryTree((object)array(
				'sessionId' => $sessionId->result, 
				'parentId' => $rootCategoryId
			));
			$categories = $result->result;
			while(count($categories)){
				$category = array_pop($categories);
				$this->db->execute($query,array($category[implode('_',array(CATEGORY,ID))],$category[NAME]));	
				if(isset($category[CHILDREN])) $categories = array_merge($categories, $category[CHILDREN]);			
			}		
		}
		catch (Exception $e)
		{
			var_dump($e);
		}

		$queries = array();
		$queries[] = 'SELECT DISTINCT ' . $rootCategoryId . ' AS ' . implode('_',array(PARENT,ID)) . ',' . CHILD . '.' . implode('_',array(CATEGORY,ID)) . ' AS ' . ID . ', Value2 AS ' . NAME . ' FROM ' . $this->config->dbprefix . TABLE_CSV . ' LEFT JOIN ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY . ' AS ' . CHILD . ' ON Value2=' . CHILD . '.' . implode('_',array(CATEGORY,NAME)) . ' WHERE (' . CHILD . '.' . implode('_',array(CATEGORY,ID)) . ' IS NULL)'; 
		for($i = 2; $i < 5 ; $i++) $queries[] = 'SELECT DISTINCT ' . PARENT . '.' . implode('_',array(CATEGORY,ID)) . ' AS ' . implode('_',array(PARENT,ID)) . ',' . CHILD . '.' . implode('_',array(CATEGORY,ID)) . ' AS ' . ID . ', Value' . ($i + 1) . ' AS ' . NAME . ' FROM ' . $this->config->dbprefix . TABLE_CSV . ' LEFT JOIN ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY . ' AS ' . PARENT . ' ON Value' . $i . '=' . PARENT . '.' . implode('_',array(CATEGORY,NAME)) . '  LEFT JOIN ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY . ' AS ' . CHILD . ' ON Value' . ($i + 1) . '=' . CHILD . '.' . implode('_',array(CATEGORY,NAME)) . ' WHERE (NOT ' . PARENT . '.' . implode('_',array(CATEGORY,ID)) . ' IS NULL) AND (' . CHILD . '.' . implode('_',array(CATEGORY,ID)) . ' IS NULL)'; 
		$result = $this->db->query(implode(' UNION ',$queries));
		$rows = $this->db->fetch_all_rows($result);
		$this->db->free_result($result);
		$columns = array(METHOD,implode('_',array(PARENT,ID)),PARAMS,STARTED,implode('_',array(CATEGORY,NAME))); 
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY_QUEUE . '('. implode(',',$columns) . ') VALUES ('. implode(',',array_fill(0,count($columns),'?')) . ')';
		foreach($rows as $row) 
			if(($row[implode('_',array(PARENT,ID))])&&(!$row[ID])){
				$parentId = $row[implode('_',array(PARENT,ID))];
				$category = array(NAME=>$row[NAME]);
				$method = 'catalog_category.create';
				$values = array($method,$parentId,json_encode($category),time(),$row[NAME]);
				$this->db->execute($query,$values);
			}
			else if((!$row[implode('_',array(PARENT,ID))])){
				$parentId = $this->config->magento_root_category_id;
				$category = array(NAME=>$row[NAME]);
				$method = 'catalog_category.create';
				$values = array($method,$parentId,json_encode($category),time(),$row[NAME]);
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
		
		// Удаляем устаревшие запросы к Magento
		$queries = array();
		foreach(array(
			TABLE_MAGENTO_CATEGORY_QUEUE,
			TABLE_MAGENTO_PRODUCT_QUEUE,
			TABLE_MAGENTO_IMAGE_QUEUE
			) as $table) $queries[]='DELETE FROM ' . $this->config->dbprefix . $table . ' WHERE ' . STARTED . '<' . (time() - $this->config->magentoexpiretime);
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);

		$apiUrl = $this->config->magento_api_url;
		$apiUser = $this->config->magento_api_user;
		$apiKey = $this->config->magento_api_key;
		
		$proxy = new SoapClient($apiUrl);	
		$sessionId = $proxy->login((object)array(
			'username' => $apiUser, 
			'apiKey' => $apiKey
		));			

		$result = $this->db->query('SELECT ' . VALUE . ' FROM ' . $this->config->dbprefix . TABLE_SETTINGS . ' WHERE ' . NAME . '="magento_circle"');
		$circle = $this->db->num_rows($result)?$this->db->fetch_single($result):0;
		$this->db->free_result($result);

		if(!$circle) {
			$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT . '(' . SOURCE . ',' . implode(',',array_keys($this->config->magento_product_fields)) . ') VALUES (' . implode(',',array_fill(0,count($this->config->magento_product_fields)+1,'?')) . ')';
			try
			{
				$result = $proxy->catalogProductList((object)array(
					'sessionId' => $sessionId->result, 
					'filters' => null
				));
				$products = $result->result;			
				foreach($products as $product){
					$source = object_to_array($product);
					$values = array(json_encode($source)); 
					foreach($this->config->magento_product_fields as $productfield) $values[] = eval('return $source["' . implode('"]["', explode('/', $productfield[1])) . '"];');
					$this->db->execute($query,$values);
				}
			}
			catch (Exception $e)
			{
				var_dump($e);
			}
							
			$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY);
			$dictionary = array();	while($row=$this->db->fetch_row($result)) $dictionary[$row[implode('_',array(CATEGORY,NAME))]] = $row[implode('_',array(CATEGORY,ID))];			
			$this->db->free_result($result);
							
			// Загрузка xls файла
			$type = explode(".", $this->config->xls);
			$ext = strtolower($type[count($type)-1]);
			$tempFile = $this->config->tempxlsfilename . getmypid() . '.' . $ext;
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
			$query = 'REPLACE ' . $this->config->dbprefix . TABLE_XLS . '(' . implode(',',array_keys($this->config->xls_fields)) . ') VALUES (' . implode(',',array_fill(0,count($this->config->xls_fields),'?')) . ')';
			foreach($sheet->getRowIterator() as $rowIterator){
				$row = $rowIterator->getRowIndex();
				$outline[$sheet->getRowDimension($row)->getOutlineLevel()]=$row;
				$values = array(); foreach($this->config->xls_fields as $xlsfield) $values[] = trim(eval($xlsfield[1]));
				$this->db->execute($query,$values);
			}
			// Удаление строк с пустой ценой
			$this->db->execute('DELETE FROM ' . $this->config->dbprefix . TABLE_XLS . ' WHERE Column11=""');
			// http://stackoverflow.com/questions/1987579/how-to-remove-warning-messages-in-php
			error_reporting(E_ERROR | E_PARSE);
			unlink($tempFile);	
	
			// Обновление записей в таблице
			$queries = array();
			$csv_fields = array(); foreach($this->config->csv_fields as $csvfield=>$values) if($values[2]) $csv_fields[$csvfield]=$values[2];
			$where = array(); foreach($this->config->csv_joins as $key=>$value) $where[] = TABLE_XLS . '.' . $key . '=' . TABLE_URL . '.' .$value;
			$queries[] = 'REPLACE ' . $this->config->dbprefix . TABLE_CSV . '(' . implode(',', array_keys($csv_fields)) . ') SELECT ' . implode(',', array_values($csv_fields)) . ' FROM ' . $this->config->dbprefix . TABLE_XLS . ' AS ' . TABLE_XLS . ' ' . $this->config->csv_join_type . ' ' . $this->config->dbprefix . TABLE_URL . ' AS ' . TABLE_URL . ' ON ' . implode(' AND ', $where);
			$queries[] = 'DELETE FROM ' . $this->config->dbprefix . TABLE_CSV . ' WHERE Value12="0"';
			$result = $this->db->multi_query(implode(';',$queries));
			$this->db->free_multi_result($result);
		
			$magento_product_template = $this->config->magento_product_template;
			$where = array(); foreach($this->config->magento_product_joins as $key=>$value) $where[] = TABLE_MAGENTO_PRODUCT . '.' . $key . '=' . TABLE_CSV . '.' .$value;
			$queries = array();
			foreach($this->config->product_join_types as $productjointype)
				$queries[] ='SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT . ' AS ' . TABLE_MAGENTO_PRODUCT . ' ' . $productjointype . ' ' . $this->config->dbprefix . TABLE_CSV . ' AS ' . TABLE_CSV . ' ON ' . implode(' AND ', $where);
			$result = $this->db->query(implode(' UNION ',$queries));
			$rows = $this->db->fetch_all_rows($result);
			$this->db->free_result($result);
			$magentokeys = array_merge(array_keys($this->config->magento_product_joins),array_values($this->config->magento_product_joins));
			$columns = array(METHOD,implode('_',array(PRODUCT,ID)),PARAMS,STARTED); for($i = 1; $i <= 6; $i++) $columns[] = IMAGE . $i;
			$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_QUEUE . '('. implode(',',$columns) . ',' . implode(',',$magentokeys) . ') VALUES ('. implode(',',array_fill(0,count($magentokeys)+count($columns),'?')) . ')';
			foreach($rows as $row){
	//		b.	В конфиге должна быть возможность указать, какие поля обновлять для карточки товара (по-умолчанию все отключены, 
	//		если например ставлю в true на обновление стоимость, то скрипт обновляет только это поле)
				$source = array_merge_recursive($row[SOURCE]?object_to_array(json_decode($row[SOURCE])):array(),object_to_array(eval($magento_product_template[$row[ID]?'update':'insert'])));
				foreach($this->config->magento_product_fields as $key=>$productfield) {
					if($productfield[2]&&$row[$productfield[2]]&&!$row[ID]) { // Для новых добавлять
						$row[$key] = $row[$productfield[2]];
						eval('$source["' . implode('"]["', explode('/', $productfield[4])) . '"] = $row[$productfield[2]];');
					}
					else if($productfield[2]&&$row[$productfield[2]]&&$productfield[3]&&$row[ID]) { // Для старых изменять если
						$row[$key] = $row[$productfield[2]];
						eval('$source["' . implode('"]["', explode('/', $productfield[1])) . '"] = $row[$productfield[2]];');
					}
					else if($row[$key]) // Иначе оставлять прежним
						eval('$source["' . implode('"]["', explode('/', $productfield[1])) . '"] = $row[$key];');
				}
				eval('$source["visibility"] = !($row["' . implode('"]||$row["',array_values($this->config->magento_product_joins)) . '"]);');
				if($this->config->magento_product_fields['collectionId'][3])
					for($i = 5; $i >= 2; $i--) if(isset($dictionary[$row["Value" . $i]])) {
						$row['categoryId'] = $dictionary[$row["Value" . $i]];
						eval('$source["' . implode('"]["', explode('/', $this->config->magento_product_fields['categoryId'])) . '"] = $row["categoryId"];');
						break;
					}
				$method = $row[implode('_',array(PRODUCT,ID))]?'catalog_product.update':'catalog_product.create';
				$productId = $row[implode('_',array(PRODUCT,ID))]?$row[implode('_',array(PRODUCT,ID))]:0;
				$values = array($method,$productId,json_encode($source),time());
				for($i = 1; $i <= 6; $i++) $values[] = $row[IMAGE . $i];
				foreach($magentokeys as $key) $values[]=$row[$key];
				$this->db->execute($query,$values);
			}
		}
		
		$circle++;
		if($circle < $this->config->magentocronperiod) {
			$this->db->execute('REPLACE ' . $this->config->dbprefix . TABLE_SETTINGS . '(' . NAME . ',' . VALUE . ') VALUES ("magento_circle","' . $circle . '")');
		}
		else {
			$this->db->execute('DELETE FROM ' . $this->config->dbprefix . TABLE_SETTINGS . ' WHERE ' . NAME . '="magento_circle"');
		}
		
		// обработка очереди картинок
		$count = ($this->config->magentocronlimit);
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_IMAGE_QUEUE . ' ORDER BY ' . STARTED . ' LIMIT ' . $count);
		$rows = $this->db->fetch_all_rows($result);
		$this->db->free_result($result);
		$magentokeys = array_merge(array_keys($this->config->magento_product_joins),array_values($this->config->magento_product_joins));
		$query = 'DELETE FROM ' . $this->config->dbprefix . TABLE_MAGENTO_IMAGE_QUEUE . ' WHERE image=?';
		foreach($rows as $row){
			$values = array($row[IMAGE]);
			try
			{
				$imageFile = $row[FILE];
				$imageData = json_decode($row[PARAMS]);
				$imageData[FILE] = ((object)array(
					'content' => base64_encode(file_get_contents($imageFile)),
					'mime' => mime_content_type($imageFile),
					'name' => IMAGE
				));
				$result = $proxy->catalogProductAttributeMediaCreate((object)array(
					'sessionId' => $sessionId->result, 
					'productId' => $row[implode('_',array(PRODUCT,ID))], 
					'data' => ((object)imageData)
				));
			}
			catch (Exception $e)
			{
				var_dump($e);
			}
			// Удаляем обработанные запросы к InSales
			$this->db->execute($query,$values);
		}
		
		// Обработка очереди товаров
		$count = ($this->config->magentocronlimit - count($rows));
		$rows = array(); 
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_QUEUE . ' WHERE image1<>"" ORDER BY ' . STARTED . ' LIMIT ' . ($count-count($rows)));
		while($row=$this->db->fetch_row($result)) $rows[] = $row;
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_QUEUE . ' WHERE image1="" ORDER BY ' . STARTED . ' LIMIT ' . ($count-count($rows)));
		while($row=$this->db->fetch_row($result)) $rows[] = $row;
		$this->db->free_result($result);
		$magentokeys = array_merge(array_keys($this->config->magento_product_joins),array_values($this->config->magento_product_joins));
		$columns1 = array(METHOD,implode('_',array(PRODUCT,ID)),PARAMS,FILE,STARTED); $columns1[] = IMAGE;
		$query = 'DELETE FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_QUEUE . ' WHERE ' . implode('=? AND ', $magentokeys) . '=?';
		$query1 = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_IMAGE_QUEUE . '('. implode(',',$columns1) . ') VALUES ('. implode(',',array_fill(0,count($columns1),'?')) . ')';
		foreach($rows as $row){
			$values = array_map(function($key) { return $row[$key]; }, $magentokeys);
			try
			{
				$productData = json_decode($row[PARAMS]);
				$productId = $row[implode('_',array(PRODUCT,ID))];
				$images = array();
				if($row[METHOD]=='catalog_product.update') {
					$result = $proxy->catalogProductUpdate((object)array(
						'sessionId' => $sessionId->result, 
						'productId' => $productId, 
						'productData' => ((object)$productData)
					));
					$result = $proxy->catalogProductAttributeMediaList((object)array(
						'sessionId' => $sessionId->result, 
						'productId' => $productId
					));
					$images = $result->result;					
				}
				if($row[METHOD]=='catalog_product.create') {
					$result = $proxy->catalogProductCreate((object)array(
						'sessionId' => $sessionId->result, 
						'productData' => ((object)$productData)
					));
					$productId = $result->result;
				}

				for($i = 1; $i <= 6; $i++) if($row[IMAGE . $i]&&!$images[$i-1]) {
					$imageData = array(
						'label' => 'image_label',
						'position' => '1',
						'types' => array('thumbnail'),
						'exclude' => '0'
					);
					$imageFile=$row[IMAGE . $i];
					$values1 = array('catalog_product_attribute_media.create',$productId,json_encode($imageData),$imageFile,time());
					$values1[] = $row[IMAGE . $i];
					$this->db->execute($query1,$values1);
				}
				// Удаляем обработанные запросы к InSales
				$this->db->execute($query,$values);
			}
			catch (Exception $e)
			{
				var_dump($e);
			}
		}
		
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}	
}