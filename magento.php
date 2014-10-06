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
		$rootCategoryId = $this->config->magento_root_category_id?$this->config->magento_root_category_id:"2";

		$client = new SoapClient($apiUrl);
		$session = $client->login($apiUser,$apiKey);
					
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY . '(' . implode('_',array(CATEGORY,ID)) . ',' . implode('_',array(CATEGORY,NAME)) . ') VALUES (?,?)';
		
		try
		{
			$categories = array($client->call($session, 'catalog_category.tree'));
			while(($category = array_pop($categories))){
				$this->db->execute($query,array($category[implode('_',array(CATEGORY,ID))],$category[NAME]));	
				if(count($category[CHILDREN])) $categories = array_merge($categories, $category[CHILDREN]);			
			}		
		}
		catch (Exception $e)
		{
			var_dump($e);
		}

		$client->endSession($session);
		
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
				
		$client = new SoapClient($apiUrl);
		$session = $client->login($apiUser,$apiKey);
					
		$columns=array(METHOD,implode('_',array(PRODUCT,ID)),implode('_',array(PRODUCT,SKU)),STARTED);
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_DOWNLOAD_QUEUE . '(' . implode(',',$columns) . ') VALUES (' . implode(',',array_fill(0,count($columns),'?')) . ')';
		try
		{
			$products = $client->call($session, 'catalog_product.list');
			foreach($products as $product){
				$productId = $product[implode('_',array(PRODUCT,ID))];
				$productSku = $product[implode('_',array(SKU))];
				$values = array('catalog_product.info',$productId,$productSku,time()); 
				$this->db->execute($query,$values);
			}
		}
		catch (Exception $e)
		{
			var_dump($e);
		}

		$client->endSession($session);
		
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function export(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();
		
		$rootCategoryId = $this->config->magento_root_category_id?$this->config->magento_root_category_id:"2";

		$queries = array();
		$queries[] = 'SELECT DISTINCT ' . $rootCategoryId . ' AS ' . implode('_',array(PARENT,ID)) . ',' . CHILD . '.' . implode('_',array(CATEGORY,ID)) . ' AS ' . implode('_',array(CHILD,ID)) . ', Value2 AS ' . NAME . ' FROM ' . $this->config->dbprefix . TABLE_CSV . ' LEFT JOIN ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY . ' AS ' . CHILD . ' ON Value2=' . CHILD . '.' . implode('_',array(CATEGORY,NAME)) . ' WHERE (' . CHILD . '.' . implode('_',array(CATEGORY,ID)) . ' IS NULL)'; 
		for($i = 2; $i < 5 ; $i++) $queries[] = 'SELECT DISTINCT ' . PARENT . '.' . implode('_',array(CATEGORY,ID)) . ' AS ' . implode('_',array(PARENT,ID)) . ',' . CHILD . '.' . implode('_',array(CATEGORY,ID)) . ' AS ' . implode('_',array(CHILD,ID)) . ', Value' . ($i + 1) . ' AS ' . NAME . ' FROM ' . $this->config->dbprefix . TABLE_CSV . ' LEFT JOIN ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY . ' AS ' . PARENT . ' ON Value' . $i . '=' . PARENT . '.' . implode('_',array(CATEGORY,NAME)) . '  LEFT JOIN ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY . ' AS ' . CHILD . ' ON Value' . ($i + 1) . '=' . CHILD . '.' . implode('_',array(CATEGORY,NAME)) . ' WHERE (NOT ' . PARENT . '.' . implode('_',array(CATEGORY,ID)) . ' IS NULL) AND (' . CHILD . '.' . implode('_',array(CATEGORY,ID)) . ' IS NULL)'; 
		$result = $this->db->query(implode(' UNION ',$queries));
		$rows = $this->db->fetch_all_rows($result);
		$this->db->free_result($result);
		$columns = array(METHOD,implode('_',array(PARENT,ID)),implode('_',array(CHILD,ID)),PARAMS,STARTED,implode('_',array(CATEGORY,NAME))); 
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY_UPLOAD_QUEUE . '('. implode(',',$columns) . ') VALUES ('. implode(',',array_fill(0,count($columns),'?')) . ')';
		foreach($rows as $row) if(($row[implode('_',array(PARENT,ID))])&&(!$row[implode('_',array(CHILD,ID))])){
			$parentId = $row[implode('_',array(PARENT,ID))];
			$childId = $row[implode('_',array(CHILD,ID))];
			$category = array(NAME=>$row[NAME]);
			$method = 'catalog_category.create';
			$values = array($method,$parentId,$childId,json_encode($category),time(),$row[NAME]);
			$this->db->execute($query,$values);
		}
				
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_CATEGORY);
		$dictionary = array();	while($row=$this->db->fetch_row($result)) $dictionary[$row[implode('_',array(CATEGORY,NAME))]] = $row[implode('_',array(CATEGORY,ID))];			
		$this->db->free_result($result);

		$magento_product_template = $this->config->magento_product_template;
		$where = array(); foreach($this->config->magento_product_joins as $key=>$value) $where[] = TABLE_MAGENTO_PRODUCT . '.' . $key . '=' . TABLE_CSV . '.' . $value;
		$queries = array();
		foreach($this->config->product_join_types as $productjointype)
			$queries[] ='SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT . ' AS ' . TABLE_MAGENTO_PRODUCT . ' ' . $productjointype . ' ' . $this->config->dbprefix . TABLE_CSV . ' AS ' . TABLE_CSV . ' ON ' . implode(' AND ', $where);
		$result = $this->db->query(implode(' UNION ',$queries));
		$rows = $this->db->fetch_all_rows($result);
		$this->db->free_result($result);
		
		$magentokeys = array_merge(array_keys($this->config->magento_product_joins),array_values($this->config->magento_product_joins));
		$columns = array(METHOD,implode('_',array(PRODUCT,ID)),implode('_',array(PRODUCT,SKU)),PARAMS,STARTED); 
		for($i = 1; $i <= 6; $i++) $columns[] = implode('',array(IMAGE,$i));
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_UPLOAD_QUEUE . '('. implode(',',array_merge($columns,$magentokeys)) . ') VALUES ('. implode(',',array_fill(0,count(array_merge($columns,$magentokeys)),'?')) . ')';
		foreach($rows as $row){
//		b.	В конфиге должна быть возможность указать, какие поля обновлять для карточки товара (по-умолчанию все отключены, 
//		если например ставлю в true на обновление стоимость, то скрипт обновляет только это поле)
			$source = array_merge_recursive($row[SOURCE]?object_to_array(json_decode($row[SOURCE])):array(),object_to_array(eval($magento_product_template)));
			foreach($this->config->magento_product_fields as $key=>$productfield) {
				if($productfield[2]&&$row[$productfield[2]]&&!$row[implode('_',array(PRODUCT,ID))]) { // Для новых добавлять
					$row[$key] = $row[$productfield[2]];
					eval('$source["' . implode('"]["', explode('/', $productfield[4])) . '"] = $row[$productfield[2]];');
				}
				else if($productfield[2]&&$row[$productfield[2]]&&($productfield[3]&&$row[implode('_',array(PRODUCT,ID))]||!$row[$key])) { // Для старых изменять если
					$row[$key] = $row[$productfield[2]];
					eval('$source["' . implode('"]["', explode('/', $productfield[1])) . '"] = $row[$productfield[2]];');
				}
				else if($row[$key]) // Иначе оставлять прежним
					eval('$source["' . implode('"]["', explode('/', $productfield[1])) . '"] = $row[$key];');
			}
			eval('$source["visibility"] = ($row["' . implode('"]&&$row["',array_values($this->config->magento_product_joins)) . '"])?"4":"0";');
			eval('$source["status"] = ($row["' . implode('"]&&$row["',array_values($this->config->magento_product_joins)) . '"])?"1":"2";');
			if($this->config->magento_product_fields['categoryId'][3])
				for($i = 5; $i >= 2; $i--) if(isset($dictionary[$row["Value" . $i]])) {
					$row['categoryId'] = $dictionary[$row["Value" . $i]];
					eval('$source["' . implode('"]["', explode('/', $this->config->magento_product_fields['categoryId'])) . '"] = $row["categoryId"];');
					break;
				}
			if(!$row['categoryId']) {
				$row['categoryId'] = $rootCategoryId;
				eval('$source["' . implode('"]["', explode('/', $this->config->magento_product_fields['categoryId'])) . '"] = $rootCategoryId;');
			}
			$method = $row[implode('_',array(PRODUCT,ID))]?'catalog_product.update':'catalog_product.create';
			$productId = $row['productId'];
			$productSku = $row['productSku'];
			$values = array($method,$productId,$productSku,json_encode($source),time());
			for($i = 16; $i <= 21; $i++) $values[] = $row[implode('',array("Value",$i))];
			foreach($magentokeys as $key) $values[]=$row[$key];
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
			TABLE_MAGENTO_CATEGORY_UPLOAD_QUEUE,
			TABLE_MAGENTO_PRODUCT_UPLOAD_QUEUE,
			TABLE_MAGENTO_IMAGE_UPLOAD_QUEUE
			) as $table) $queries[] = 'TRUNCATE ' . $this->config->dbprefix . $table;		
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function task2(){
		$start = microtime(true);
		$this->db->connect();
		
		$result = $this->db->query('SELECT MAX(' . ID . ') FROM ' . $this->config->dbprefix . TABLE_TASK_QUEUE);
		$taskId = $this->db->fetch_single($result); $taskId=$taskId?$taskId+1:1;
		$this->db->free_result($result);
		$columns = array(ID,CLAZZ,METHOD);
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_TASK_QUEUE . '(' . implode(',',$columns) . ') VALUES (' . implode(',',array_fill(0,count($columns),'?')) . ')';
		$this->db->execute($query,array($taskId++,MAGENTO,'clear'));
		$this->db->execute($query,array($taskId++,MAGENTO,'category'));
		$this->db->execute($query,array($taskId++,MAGENTO,'product'));
				
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
			TABLE_MAGENTO_CATEGORY_UPLOAD_QUEUE,
			TABLE_MAGENTO_PRODUCT_UPLOAD_QUEUE,
			TABLE_MAGENTO_IMAGE_UPLOAD_QUEUE
			) as $table) $queries[]='DELETE FROM ' . $this->config->dbprefix . $table . ' WHERE ' . STARTED . '<' . (time() - $this->config->magentoexpiretime);
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);

		$apiUrl = $this->config->magento_api_url;
		$apiUser = $this->config->magento_api_user;
		$apiKey = $this->config->magento_api_key;
		
		$client = new SoapClient($apiUrl);
		$session = $client->login($apiUser,$apiKey);
					
		// обработка очереди товаров
		$count = ($this->config->magentocronlimit);
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_DOWNLOAD_QUEUE . ' ORDER BY ' . STARTED . ' LIMIT ' . $count);
		$rows = $this->db->fetch_all_rows($result);
		$this->db->free_result($result);
		$columns=array_merge(array(implode('_',array(PRODUCT,ID)),implode('_',array(PRODUCT,SKU)),SOURCE),array_keys($this->config->magento_product_fields));
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT . '(' . implode(',',$columns) . ') VALUES (' . implode(',',array_fill(0,count($columns),'?')) . ')';
		$columns1=array(implode('_',array(PRODUCT,ID)),implode('_',array(PRODUCT,SKU)));
		$query1 = 'DELETE FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_DOWNLOAD_QUEUE . ' WHERE ' . implode('=? AND ', $columns1) . '=?';
		try
		{
			foreach($rows as $row){
				$productId = $row[implode('_',array(PRODUCT,ID))];
				$productSku = $row[implode('_',array(PRODUCT,SKU))];
								
				// помечаем сразу как обработанный, чтобы не зависать на одной ошибке
				$values1 = array($productId,$productSku); 
				$this->db->execute($query1,$values1);
				
				$source = (array)($client->call($session, 'catalog_product.info', $productId)); // Returns: Array of catalogProductReturnEntity
				$values = array($productId,$productSku,json_encode($source)); 
				
				foreach($this->config->magento_product_fields as $productfield) $values[] = eval('return $source["' . implode('"]["', explode('/', $productfield[1])) . '"];');
				
				$this->db->execute($query,$values);
				echo "<pre>Product $productId $productSku download complite.</pre>";
			}
		}
		catch (Exception $e)
		{
			var_dump($e);
		}
		
		// обработка очереди картинок
		$count = ($this->config->magentocronlimit);
		$finfo = new finfo(FILEINFO_MIME);
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_IMAGE_UPLOAD_QUEUE . ' ORDER BY ' . STARTED . ' LIMIT ' . $count);
		$rows = $this->db->fetch_all_rows($result);
		$this->db->free_result($result);
		$magentokeys = array_merge(array_keys($this->config->magento_product_joins),array_values($this->config->magento_product_joins));
		$query = 'DELETE FROM ' . $this->config->dbprefix . TABLE_MAGENTO_IMAGE_UPLOAD_QUEUE . ' WHERE image=?';
		foreach($rows as $row){
			
			// помечаем сразу как обработанный, чтобы не зависать на одной ошибке
			$values = array($row[IMAGE]);
			$this->db->execute($query,$values);
			
			try
			{
				$productId = $row[implode('_',array(PRODUCT,ID))];
				$productSku = $row[implode('_',array(PRODUCT,SKU))];
				$imageFile = $row[implode('_',array(IMAGE,FILE))];
				$imageData = (array)json_decode($row[PARAMS]);
				
				$content = file_get_contents($imageFile);
				if(!$content) continue;
				
				$mime = explode(';',$finfo->file($imageFile));
				$imageData[FILE] = array(
					'content' => base64_encode($content),
					'mime' => $mime[0],
					'name' => IMAGE
				);
				$result = $client->call($session,'catalog_product_attribute_media.create',array($productId,$imageData));
				echo "<pre>Media $imageFile upload complite.</pre>";
			}
			catch (Exception $e)
			{
				var_dump($e);
			}
		}
			
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_DOWNLOAD_QUEUE);
		$count = $this->db->fetch_single($result);
		$this->db->free_result($result);
		if($count) return;

		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_UPLOAD_QUEUE);
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
			$this->db->execute($query,array($taskId++,MAGENTO,'export'));
			return;
		}
		
		$attributeSets = $client->call($session, 'product_attribute_set.list');
		$attributeSet = current($attributeSets);

		// Обработка очереди товаров
		$count = ($this->config->magentocronlimit);
		$rows = array(); 
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_UPLOAD_QUEUE . ' WHERE image1<>"" ORDER BY ' . STARTED . ' LIMIT ' . ($count-count($rows)));
		$rows = array_merge($rows,$this->db->fetch_all_rows($result));
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_UPLOAD_QUEUE . ' WHERE image1="" ORDER BY ' . STARTED . ' LIMIT ' . ($count-count($rows)));
		$rows = array_merge($rows,$this->db->fetch_all_rows($result));
		$this->db->free_result($result);
		$magentokeys = array_merge(array_keys($this->config->magento_product_joins),array_values($this->config->magento_product_joins));
		$columns1 = array(METHOD,implode('_',array(PRODUCT,ID)),implode('_',array(PRODUCT,SKU)),implode('_',array(IMAGE,FILE)),PARAMS,STARTED,IMAGE);
		$query = 'DELETE FROM ' . $this->config->dbprefix . TABLE_MAGENTO_PRODUCT_UPLOAD_QUEUE . ' WHERE ' . implode('=? AND ', $magentokeys) . '=?';
		$query1 = 'REPLACE ' . $this->config->dbprefix . TABLE_MAGENTO_IMAGE_UPLOAD_QUEUE . '('. implode(',',$columns1) . ') VALUES ('. implode(',',array_fill(0,count($columns1),'?')) . ')';
		foreach($rows as $row){
			
			// помечаем сразу как обработанный, чтобы не зависать на одной ошибке
			$values = array(); foreach($magentokeys as $key) $values[] = $row[$key];
			$this->db->execute($query,$values);
			
			try
			{
				$productData = (array)json_decode($row[PARAMS]);
				$productId = $row[implode('_',array(PRODUCT,ID))];
				$productSku = $row[implode('_',array(PRODUCT,SKU))];
				$images = array();
				if($row[METHOD]=='catalog_product.update') {
					$result = $client->call($session, $row[METHOD], array($productId, $productData));
					$images = $client->call($session, 'catalog_product_attribute_media.list', $productId);
				}
				if($row[METHOD]=='catalog_product.create') {
					$productId = $client->call($session, $row[METHOD], array('simple', $attributeSet['set_id'], $productSku, $productData));	
				}

				for($i = 1; $i <= 6; $i++) if($row[implode('',array(IMAGE,$i))]&&!$images[$i-1]) {
					$imageData = array(
						'label' => $productData[NAME],
						'position' => '1',
						'types' => array('image','small_image','thumbnail'),
						'exclude' => '0'
					);
					$imageFile=$row[implode('',array(IMAGE,$i))];
					$values1 = array(
						'catalog_product_attribute_media.create',
						$productId,
						$productSku,
						$imageFile,
						json_encode($imageData),
						time()
					);
					$values1[] = $row[implode('',array(IMAGE,$i))];
					$this->db->execute($query1,$values1);
				}
				echo "<pre>Product $productId $productSku upload complite.</pre>";
			}
			catch (Exception $e)
			{
				var_dump($e);
			}
		}
		
		$client->endSession($session);
		
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}	
}