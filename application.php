<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru

/** Include PHPExcel */
require_once dirname(__FILE__) . '/bower_components/PHPExcel/Classes/PHPExcel.php';

require_once( dirname(__FILE__) . '/defines.php' );
require_once( dirname(__FILE__) . '/functions.php' );
require_once( dirname(__FILE__) . '/configuration.php' );
require_once( dirname(__FILE__) . '/database.php' );
require_once( dirname(__FILE__) . '/insales.php' );
require_once( dirname(__FILE__) . '/magento.php' );
require_once( dirname(__FILE__) . '/factory.php' );


class JApplication {
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
			self::$instance = new JApplication;
		}
		return self::$instance;
	}

	private function drop_table_if_exists(){
		$queries = array();
		foreach(array(
			TABLE_MAGENTO_CATEGORY,
			TABLE_MAGENTO_PRODUCT,
			TABLE_INSALES_COLLECTION,
			TABLE_INSALES_PRODUCT,
			TABLE_CSV,
			TABLE_XLS,
			TABLE_URL,
			TABLE_PAGE_DOWNLOAD_QUEUE,
			TABLE_IMAGE_DOWNLOAD_QUEUE,
			TABLE_INSALES_COLLECTION_UPLOAD_QUEUE,
			TABLE_INSALES_PRODUCT_UPLOAD_QUEUE,
			TABLE_INSALES_IMAGE_UPLOAD_QUEUE,
			TABLE_MAGENTO_CATEGORY_UPLOAD_QUEUE,
			TABLE_MAGENTO_PRODUCT_UPLOAD_QUEUE,
			TABLE_MAGENTO_PRODUCT_DOWNLOAD_QUEUE,
			TABLE_MAGENTO_IMAGE_UPLOAD_QUEUE,
			TABLE_SETTINGS
			) as $table) $queries[] = 'DROP TABLE IF EXISTS ' . $this->config->dbprefix . $table;
		return $queries;
	}
	private function create_table_if_not_exists(){
		$queries = array();
		$insaleskeys = array();
		$magentokeys = array();
		foreach($this->config->insales_product_joins as $key=>$value) {
			$insaleskeys[$key]=$this->config->insales_product_fields[$key];
			$insaleskeys[$value]=$this->config->csv_fields[$value];
		}
		foreach($this->config->magento_product_joins as $key=>$value) {
			$magentokeys[$key]=$this->config->magento_product_fields[$key];
			$magentokeys[$value]=$this->config->csv_fields[$value];
		}
		$specifications = array(
			TABLE_TASK_QUEUE => array(
				ID . ' INTEGER',
				CLAZZ . ' VARCHAR(100)',
				METHOD . ' VARCHAR(100)',
				'PRIMARY KEY (' . ID . ')'
			),
			TABLE_MAGENTO_CATEGORY => array(
				implode('_',array(CATEGORY,ID)) . ' INTEGER',
				implode('_',array(CATEGORY,NAME)) . ' VARCHAR(100)',
				'PRIMARY KEY (' . implode('_',array(CATEGORY,ID)) . ')'
			),
			TABLE_MAGENTO_PRODUCT => array(
				implode('_',array(PRODUCT,ID)) . ' VARCHAR(100)',
				implode('_',array(PRODUCT,SKU)) . ' VARCHAR(100)',
				SOURCE . ' TEXT',
				'PRIMARY KEY (' . implode(',', $this->config->magento_product_keys) . ')',
				'INDEX (' . implode(',', array_keys($this->config->magento_product_joins)) . ')'
			),
			TABLE_INSALES_COLLECTION => array(
				implode('_',array(COLLECTION,ID)) . ' INTEGER',
				implode('_',array(COLLECTION,TITLE)) . ' VARCHAR(100)',
				'PRIMARY KEY (' . implode('_',array(COLLECTION,ID)) . ')'
			),
			TABLE_INSALES_PRODUCT => array(
				SOURCE . ' TEXT',
				'PRIMARY KEY (' . implode(',', $this->config->insales_product_keys) . ')',
				'INDEX (' . implode(',', array_keys($this->config->insales_product_joins)) . ')'
			),
			TABLE_CSV => array(
				'PRIMARY KEY (' . implode(',', $this->config->csv_keys) . ')',
				'INDEX (' . implode(',', array_values($this->config->insales_product_joins)) . ')',
				'INDEX (' . implode(',', array_values($this->config->magento_product_joins)) . ')'
			),
			TABLE_XLS => array(
				'PRIMARY KEY (' . implode(',', $this->config->xls_keys) . ')',
				'INDEX (' . implode(',', array_keys($this->config->csv_joins)) . ')'
			),
			TABLE_URL => array(
				'PRIMARY KEY (' . implode(',', $this->config->url_keys) . ')',
				'INDEX (' . implode(',', array_values($this->config->csv_joins)) . ')'
			),
			TABLE_PAGE_DOWNLOAD_QUEUE => array(
				URL . ' VARCHAR(255)',
				LOADED . ' INTEGER',
				'PRIMARY KEY (' . URL . ')'			
			),
			TABLE_IMAGE_DOWNLOAD_QUEUE => array(
				URL . ' VARCHAR(255)',
				FILE . ' VARCHAR(255)',
				LOADED . ' INTEGER',
				'PRIMARY KEY (' . FILE . ')'			
			),
			TABLE_SETTINGS => array(
				NAME . ' VARCHAR(100)',
				VALUE . ' VARCHAR(100)',
				'PRIMARY KEY (' . NAME . ')'
			),
			
			TABLE_INSALES_COLLECTION_UPLOAD_QUEUE => array(
				METHOD . ' VARCHAR(50)',
				PATH . ' VARCHAR(100)',
				PARAMS . ' TEXT',
				STARTED . ' INTEGER',
				implode('_',array(COLLECTION,TITLE)) . ' VARCHAR(100)',
				'PRIMARY KEY (' . implode('_',array(COLLECTION,TITLE)) . ')'
			),
			TABLE_INSALES_PRODUCT_UPLOAD_QUEUE => array(
				METHOD . ' VARCHAR(50)',
				PATH . ' VARCHAR(100)',
				PARAMS . ' TEXT',
				STARTED . ' INTEGER',
				ID . ' INTEGER',
				implode('',array(IMAGE,1)) . ' VARCHAR(255)',
				implode('',array(IMAGE,2)) . ' VARCHAR(255)',
				implode('',array(IMAGE,3)) . ' VARCHAR(255)',
				implode('',array(IMAGE,4)) . ' VARCHAR(255)',
				implode('',array(IMAGE,5)) . ' VARCHAR(255)',
				implode('',array(IMAGE,6)) . ' VARCHAR(255)',
				'PRIMARY KEY (' . implode(',', array_keys($insaleskeys)) . ')'			
			),
			TABLE_INSALES_IMAGE_UPLOAD_QUEUE => array(
				METHOD . ' VARCHAR(50)',
				PATH . ' VARCHAR(100)',
				PARAMS . ' TEXT',
				STARTED . ' INTEGER',
				IMAGE . ' VARCHAR(255)',
				'PRIMARY KEY (image)'			
			),
			
			TABLE_MAGENTO_CATEGORY_UPLOAD_QUEUE => array(
				METHOD . ' VARCHAR(50)',
				implode('_',array(PARENT,ID)) . ' VARCHAR(50)',
				implode('_',array(CHILD,ID)) . ' VARCHAR(50)',
				PARAMS . ' TEXT',
				STARTED . ' INTEGER',
				implode('_',array(CATEGORY,NAME)) . ' VARCHAR(100)',
				'PRIMARY KEY (' . implode('_',array(CATEGORY,NAME)) . ')'
			),
			TABLE_MAGENTO_PRODUCT_UPLOAD_QUEUE => array(
				METHOD . ' VARCHAR(50)',
				implode('_',array(PRODUCT,ID)) . ' VARCHAR(50)',
				implode('_',array(PRODUCT,SKU)) . ' VARCHAR(50)',
				PARAMS . ' TEXT',
				STARTED . ' INTEGER',
				implode('',array(IMAGE,1)) . ' VARCHAR(255)',
				implode('',array(IMAGE,2)) . ' VARCHAR(255)',
				implode('',array(IMAGE,3)) . ' VARCHAR(255)',
				implode('',array(IMAGE,4)) . ' VARCHAR(255)',
				implode('',array(IMAGE,5)) . ' VARCHAR(255)',
				implode('',array(IMAGE,6)) . ' VARCHAR(255)',
				'PRIMARY KEY (' . implode(',', array_keys($magentokeys)) . ')'			
			),
			TABLE_MAGENTO_PRODUCT_DOWNLOAD_QUEUE => array(
				METHOD . ' VARCHAR(50)',
				implode('_',array(PRODUCT,ID)) . ' VARCHAR(50)',
				implode('_',array(PRODUCT,SKU)) . ' VARCHAR(50)',
				STARTED . ' INTEGER',
				'PRIMARY KEY (' . implode(',', array(implode('_',array(PRODUCT,ID)),implode('_',array(PRODUCT,SKU)))) . ')'			
			),
			TABLE_MAGENTO_IMAGE_UPLOAD_QUEUE => array(
				METHOD . ' VARCHAR(50)',
				implode('_',array(PRODUCT,ID)) . ' VARCHAR(50)',
				implode('_',array(PRODUCT,SKU)) . ' VARCHAR(50)',
				implode('_',array(IMAGE,FILE)) . ' VARCHAR(255)',
				PARAMS . ' TEXT',
				STARTED . ' INTEGER',
				IMAGE . ' VARCHAR(255)',
				'PRIMARY KEY (' . IMAGE . ')'			
			)
		);
		$list = array(TABLE_INSALES_PRODUCT_UPLOAD_QUEUE=>$insaleskeys,TABLE_MAGENTO_PRODUCT_UPLOAD_QUEUE=>$magentokeys);
		foreach(array(TABLE_MAGENTO_PRODUCT,TABLE_INSALES_PRODUCT,TABLE_CSV,TABLE_XLS,TABLE_URL) as $table) {
			$fields = implode('_',array($table,'fields')); $list[$table] = $this->config->$fields;
		}
		foreach($list as $table=>$keys)	foreach($keys as $field=>$values) $specifications[$table][] = $field . ' ' . $values[0];
		foreach($specifications as $table=>$values) $queries[] = 'CREATE TABLE IF NOT EXISTS ' . $this->config->dbprefix . $table . '(' . implode(',', $values) . ') CHARACTER SET utf8 COLLATE utf8_general_ci';
		return $queries;
	}
	
	public function rebuild_database(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();
		$this->db->multi_query(implode(';',array_merge($this->drop_table_if_exists(), $this->create_table_if_not_exists())));
		$this->db->free_multi_result($result);
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function info(){
		set_time_limit(0);
		$this->db->connect();
		
		$columns = array();
		foreach(array(
			TABLE_MAGENTO_PRODUCT,
			TABLE_INSALES_PRODUCT,
			TABLE_CSV,
			TABLE_XLS,
			TABLE_URL
			) as $table) {
			$columns[$table] = array();
			$fields = $table . '_fields';
			foreach($this->config->$fields as $field=>$values) $columns[$table][] = $field . ' ' . $values[0];
		}
		// Создаём таблицы в случае их отсутствия в базе данных
		$result = $this->db->multi_query(implode(';',$this->create_table_if_not_exists()));
		$this->db->free_multi_result($result);

		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_IMAGE_DOWNLOAD_QUEUE . ' WHERE ' . LOADED . '="0"');
		$queue = $this->db->fetch_single($result);
		$this->db->free_result($result);
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_IMAGE_DOWNLOAD_QUEUE);
		$queue_total = $this->db->fetch_single($result);
		$this->db->free_result($result);
		echo "<pre>Image queue: <b>$queue/$queue_total</b> - $queue картинок в очереди ожидает загрузки , $queue_total - всего известных ссылок на картинки с сайта</pre>";
		
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_PAGE_DOWNLOAD_QUEUE . ' WHERE ' . LOADED . '<' . (time()-$this->config->pageupdatetime));
		$queue = $this->db->fetch_single($result);
		$this->db->free_result($result);
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_PAGE_DOWNLOAD_QUEUE);
		$queue_total = $this->db->fetch_single($result);
		$this->db->free_result($result);
		echo "<pre>Page queue: <b>$queue/$queue_total</b> - $queue страниц в очереди ожидает загрузки, $queue_total – всего известных ссылок на страницы сайта</pre>";
		
		foreach(array(
			INSALES=>TABLE_INSALES_PRODUCT_UPLOAD_QUEUE,
			MAGENTO=>TABLE_MAGENTO_PRODUCT_UPLOAD_QUEUE
			) as $shop=>$table) {
			$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . $table);
			$queue = $this->db->fetch_single($result);
			$this->db->free_result($result);
			echo "<pre>$shop queue: <b>$queue</b> - $queue карточек товаров в очереди ожидает загрузки на $shop</pre>";
		}
		
		foreach(array(
			INSALES=>TABLE_INSALES_IMAGE_UPLOAD_QUEUE,
			MAGENTO=>TABLE_MAGENTO_IMAGE_UPLOAD_QUEUE
			) as $shop=>$table) {
			$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . $table);
			$queue = $this->db->fetch_single($result);
			$this->db->free_result($result);
			echo "<pre>$shop image queue: <b>$queue</b> - $queue изображений товаров в очереди ожидает загрузки на $shop</pre>";
		}
		
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_URL );
		$count = $this->db->fetch_single($result);
		$this->db->free_result($result);
		echo "<pre>Url records downloaded: <b>$count</b> - количество карточек товаров уже имеется в базе данных по результатам парсинга страниц сайта</pre>";
		
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_XLS );
		$count = $this->db->fetch_single($result);
		$this->db->free_result($result);
		echo "<pre>Xls records downloaded: <b>$count</b> - количество загруженных строк из xls файла</pre>";
		
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_CSV );
		$count = $this->db->fetch_single($result);
		$this->db->free_result($result);
		echo "<pre>Csv records created: <b>$count</b> - количество созданых строк в csv файле</pre>";
		
		foreach(array(
			INSALES=>TABLE_INSALES_PRODUCT,
			MAGENTO=>TABLE_MAGENTO_PRODUCT
			) as $shop=>$table) {
			$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . $table );
			$count = $this->db->fetch_single($result);
			$this->db->free_result($result);
			echo "<pre>$shop records downloaded: <b>$count</b> - количество загруженных карточек товара из $shop</pre>";
		}
		
		$this->db->disconnect();
	}
	public function page_curl_cron(){
		$start = microtime(true);
		set_time_limit(0);
		$default = parse_url($this->config->url);	
		$this->db->connect();
		// Очищаем таблицу от ненужных ссылок
		$queries = array();
		$queries[] = 'DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE_DOWNLOAD_QUEUE . ' WHERE NOT ' . URL . ' LIKE "%' . $default['host'] . '%"';
		// Удаляем неправильные ссылки
		foreach(array("jpg","jpeg","gif","png","tiff","pdf","doc","xls","ppt","docx","xlsx","pptx","avi","mov","mpg","mpeg","swf","exe","msi","zip","swf") as $ext) $queries[] = 'DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE_DOWNLOAD_QUEUE . ' WHERE ' . URL . ' LIKE "%.' . $ext .'%"';
		// Добавляем ссылку на сайт
		$queries[] = 'INSERT IGNORE ' . $this->config->dbprefix . TABLE_PAGE_DOWNLOAD_QUEUE . '(' . URL . ',' . LOADED . ') VALUES ("' . safe($this->config->url) . '",0)';
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
		// Получаем список ссылок для задания
		// В первую очередь обрабатываются ссылки, содержащие в себе слово product
		$records = array(); 
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_PAGE_DOWNLOAD_QUEUE . ' WHERE ' . LOADED . '<' . (time()-$this->config->pageupdatetime) .' AND ' . URL . ' LIKE "%product%" ORDER BY ' . LOADED . ' LIMIT ' . ($this->config->pagecronlimit - count($records)));
		while($row=$this->db->fetch_row($result)) $records[]=$row[URL];
		$this->db->free_result($result);
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_PAGE_DOWNLOAD_QUEUE . ' WHERE ' . LOADED . '<' . (time()-$this->config->pageupdatetime) .' AND NOT ' . URL . ' LIKE "%product%" ORDER BY ' . LOADED . ' LIMIT ' . ($this->config->pagecronlimit - count($records)));
		while($row=$this->db->fetch_row($result)) $records[]=$row[URL];
		$this->db->free_result($result);
		foreach($records as $url){
			$queries = array();
			$pid = -1;
			// The pcntl_fork() function creates a child process that differs from the parent process only in its PID and PPID.
			// Please see your system's fork(2) man page for specific details as to how fork works on your system.
			if($this->config->parallel) $pid = pcntl_fork();
			// $pid === -1 failed to fork
			// $pid == 0, this is the child thread
			// $pid != 0, this is the parent thread
			if ($pid > 0) continue;
			
			$html = file_get_contents($url);			
			if(!$html) {
				// Исклучаем из дальнейшей загрузки отсутствующие страницы
				$queries[]='DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE_DOWNLOAD_QUEUE . ' WHERE ' . URL . '="' . safe($url) . '"';
				// $pid === -1 failed to fork
				// $pid == 0, this is the child thread
				// $pid != 0, this is the parent thread
				$result = $this->db->multi_query(implode(';',$queries));
				$this->db->free_multi_result($result);
				if(!$pid) break;
				continue;
			}
			// http://stackoverflow.com/questions/3523409/domdocument-encoding-problems-characters-transformed/12846243#12846243
			// http://stackoverflow.com/questions/1148928/disable-warnings-when-loading-non-well-formed-html-by-domdocument-php
			$doc = new DOMDocument('1.0','utf-8');
			libxml_use_internal_errors(true);
			$doc->loadHTML($html);
			libxml_clear_errors();
			$xpath = new DOMXpath($doc);
			
			// Добавляем в поиск все ссылки на странице, на том же домене
			$links = array();
			$elements = $xpath->query('//a[@href]//@href');
			if (!is_null($elements)) {
				foreach ($elements as $element) {
					$parse = parse_url($element->nodeValue);
					if(isset($parse['fragment'])) unset($parse['fragment']);
					if(isset($parse['query'])) unset($parse['query']);
					$addr = explode('/',unparse_url($parse,$default));
					while(!$addr[count($addr)-1]) array_pop($addr);
					$links[implode('/',$addr)] = 0;
				}
			}
			foreach($links as $link=>$time) $queries[]='INSERT IGNORE ' . $this->config->dbprefix . TABLE_PAGE_DOWNLOAD_QUEUE . '(' . URL . ',' . LOADED . ') VALUES ("' . $link . '",' . $time . ')';
			
			// Обрабатываем поля на странице
			$fields = array();
			foreach($this->config->url_fields as $urlfield=>$values){
				$elements = $xpath->query($values[1]);
				$tokens = array();
				if (!is_null($elements)) {
					foreach ($elements as $element) $tokens[] = preg_replace($values[2], $values[3], $element->nodeValue);
				}
				$fields[$urlfield] = safe(trim(implode('',$tokens)));
			}

			// Обрабатываем транслит изображений
			for($i = 1; $i <= 6; $i++){
				$src = $fields[IMAGE . $i];
				if($src){
					$imageUrl = unparse_url(parse_url($src),$default);
					$type = explode(".", $imageUrl);
					$ext = strtolower($type[count($type)-1]);
					$file = $this->config->imagedir . $fields["translit"] . '_' . $i . '.' . $ext;
					$fields[IMAGE . $i] = $file;
					$queries[]='INSERT IGNORE ' . $this->config->dbprefix . TABLE_IMAGE_DOWNLOAD_QUEUE . '(' . URL . ',' . FILE . ',' . LOADED . ') VALUES ("' . safe($imageUrl) . '","' . safe($file) . '",0)';
				}
			}
			
			$queries[]='REPLACE ' . $this->config->dbprefix . TABLE_URL . '(' . implode(',', array_keys($fields)) . ') VALUES ("' . implode('","', array_values($fields)) . '")';
			$queries[]='REPLACE ' . $this->config->dbprefix . TABLE_PAGE_DOWNLOAD_QUEUE . '(' . URL . ',' . LOADED . ') VALUES ("' . safe($url) . '",' . time() . ')';

			$result = $this->db->multi_query(implode(';',$queries));
			$this->db->free_multi_result($result);
			echo "<pre><a href='$url' target='_blank'>$url</a> complite.</pre>";

			// $pid === -1 failed to fork
			// $pid == 0, this is the child thread
			// $pid != 0, this is the parent thread
			if(!$pid) break;
		}
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function image_curl_cron(){
		$start = microtime(true);
		set_time_limit(0);
		//get watermark
		$watermark = $this->config->watermark;
		//get watermark size
		$watermarkSize = getimagesize($watermark);
		$watermarkWidth = $watermarkSize[0];
		$watermarkHeight = $watermarkSize[1];		
		//get watermark extension
		$type = explode(".", $watermark);
		$ext = strtolower($type[count($type)-1]);
		$ext = (!in_array($ext, array("jpeg","png","gif"))) ? "jpeg" : $ext;		
		//create watermark source
		$func = "imagecreatefrom".$ext;
		$watermarkSource = $func($watermark);

		$padding = 0; //padding from image border
		
		$this->db->connect();
		$queries = array();
		// Очищаем таблицу от ненужных ссылок
		// Удаляем неправильные ссылки
		foreach(array("html","htm","php","asp","pdf","doc","doc","xls","ppt","docx","xlsx","pptx","avi","mov","mpg","mpeg","swf","exe","msi","zip","swf") as $ext) $queries[] = 'DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE_DOWNLOAD_QUEUE . ' WHERE ' . URL . ' LIKE "%.' . $ext .'%"';
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
		// Получаем список ссылок для задания
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_IMAGE_DOWNLOAD_QUEUE . ' WHERE ' . LOADED . '="0" LIMIT ' . $this->config->imagecronlimit);
		$records = array(); while($row=$this->db->fetch_row($result)) $records[$row[FILE]]=$row[URL];
		$this->db->free_result($result);
		$query='REPLACE ' . $this->config->dbprefix . TABLE_IMAGE_DOWNLOAD_QUEUE . '(' . URL . ',' . FILE . ',' . LOADED . ') VALUES (?,?,?)';
		foreach($records as $file=>$url){

			// помечаем файл сразу как обработанный, чтобы не зависать на одной ошибке
			$this->db->execute($query, array($url,$file,time()));
			
			$pid = -1;
			// The pcntl_fork() function creates a child process that differs from the parent process only in its PID and PPID.
			// Please see your system's fork(2) man page for specific details as to how fork works on your system.
			if($this->config->parallel) $pid = pcntl_fork();
			// $pid === -1 failed to fork
			// $pid == 0, this is the child thread
			// $pid != 0, this is the parent thread
			if ($pid > 0) continue;

			$type = explode(".", $url);
			$ext = strtolower($type[count($type)-1]);
			$ext = (!in_array($ext, array("jpeg","png","gif"))) ? "jpeg" : $ext;
			$tempFile = $this->config->tempimagefilename . getmypid() . '.' . $ext;
			// http://stackoverflow.com/questions/1987579/how-to-remove-warning-messages-in-php
			error_reporting(E_ERROR | E_PARSE);
			unlink($tempFile);	
			// Загрузка и сохранение файла на диске
			$image = file_get_contents($url);
			if(!$image) {
				// Исклучаем из дальнейшей загрузки отсутствующие страницы
				$this->db->execute('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE_DOWNLOAD_QUEUE . ' WHERE ' . URL . '= ? AND ' . FILE . '= ?',array($url,$file));
				// $pid === -1 failed to fork
				// $pid == 0, this is the child thread
				// $pid != 0, this is the parent thread
				if(!$pid) break;
				continue;
			}
			file_put_contents($tempFile, $image);			
			$size = getimagesize($tempFile);
			$width = $size[0];
			$height = $size[1];
			
			try {
				$func = "imagecreatefrom".$ext;
				$source = $func($tempFile);
				//create output resource
				$output = imagecreatetruecolor($width, $height);
				//to preserve PNG transparency
				//saving all full alpha channel information
				imagesavealpha($output, true);
				//setting completely transparent color
				$transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
				//filling created image with transparent color
				imagefill($output, 0, 0, $transparent);			
				//copy source to destination
				imagecopyresampled( $output, $source,  0, 0, 0, 0, 
									$width, $height, $width, $height);			
				//let's make watermark 1/4 of image size
				$wanted_width = round($width/4);
				$wanted_height = round($height/4);
				if(($watermarkWidth/$wanted_width) < ($watermarkHeight/$wanted_height))
				{
					//resize by height
					$wanted_width = ($watermarkWidth*$wanted_height)/$watermarkHeight;
				}
				else
				{
					//resize by width
					$wanted_height = ($watermarkHeight*$wanted_width)/$watermarkWidth;
				}
				//bottom right
				$dst_x = $width - $padding - $wanted_width;
				$dst_y = $height-$padding-$wanted_height;
				//copy watermark
				imagecopyresampled( $output, $watermarkSource,  $dst_x, $dst_y, 0, 0, 
									$wanted_width, $wanted_height, $watermarkWidth, $watermarkHeight);
				$func = IMAGE.$ext;
				$func($output, $file); 
			}
			catch (Exception $e)
			{
			}

			// http://stackoverflow.com/questions/1987579/how-to-remove-warning-messages-in-php
			error_reporting(E_ERROR | E_PARSE);
			unlink($tempFile);	
			echo "<pre><a href='$url' target='_blank'>$url</a> complite.</pre>";
			
			// $pid === -1 failed to fork
			// $pid == 0, this is the child thread
			// $pid != 0, this is the parent thread
			if(!$pid) break;
		}
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	// Удаление всех записей из таблицы
	private function clear_table($table){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();
		$this->db->execute('TRUNCATE ' . $this->config->dbprefix . $table);
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	// Удаление всех записей из таблицы
	public function clear_csv(){ $this->clear_table(TABLE_CSV); }
	public function clear_xls(){ $this->clear_table(TABLE_XLS); }
	public function clear_url(){ $this->clear_table(TABLE_URL); }
	public function clear_page(){ $this->clear_table(TABLE_PAGE_DOWNLOAD_QUEUE); }
	public function clear_image(){ $this->clear_table(TABLE_IMAGE_DOWNLOAD_QUEUE); }
	public function clear_magento_product(){ $this->clear_table(TABLE_MAGENTO_PRODUCT); }
	public function clear_magento_category(){ $this->clear_table(TABLE_MAGENTO_CATEGORY); }
	public function clear_insales_product(){ $this->clear_table(TABLE_INSALES_PRODUCT); }
	public function clear_insales_collection(){ $this->clear_table(TABLE_INSALES_COLLECTION); }
	public function clear_settings(){ $this->clear_table(TABLE_SETTINGS); }
	public function clear_insales(){ 
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();
		$queries = array();
		foreach(array(
			TABLE_INSALES_COLLECTION,
			TABLE_INSALES_PRODUCT,
			TABLE_INSALES_COLLECTION_UPLOAD_QUEUE,
			TABLE_INSALES_PRODUCT_UPLOAD_QUEUE,
			TABLE_INSALES_IMAGE_UPLOAD_QUEUE
			) as $table) $queries[]='TRUNCATE ' . $this->config->dbprefix . $table;
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	public function clear_magento(){ 
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();
		$queries = array();
		foreach(array(
			TABLE_MAGENTO_PRODUCT,
			TABLE_MAGENTO_CATEGORY,
			TABLE_MAGENTO_PRODUCT_DOWNLOAD_QUEUE,
			TABLE_MAGENTO_CATEGORY_UPLOAD_QUEUE,
			TABLE_MAGENTO_PRODUCT_UPLOAD_QUEUE,
			TABLE_MAGENTO_IMAGE_UPLOAD_QUEUE
			) as $table) $queries[]='TRUNCATE ' . $this->config->dbprefix . $table;
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
		
	public function import_url(){
		$start = microtime(true);
		set_time_limit(0);
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	// Импорт записей из файла в таблицу
	public function import_xls(){
		$start = microtime(true);
		set_time_limit(0);
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
		$this->db->disconnect();
		// http://stackoverflow.com/questions/1987579/how-to-remove-warning-messages-in-php
		error_reporting(E_ERROR | E_PARSE);
		unlink($tempFile);	
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}

	// Импорт записей из файла в таблицу
	public function import_csv(){
		$start = microtime(true);
		set_time_limit(0);
		// http://stackoverflow.com/questions/3895819/csv-export-import-with-phpexcel
		$inputFileType = PHPExcel_IOFactory::identify($this->config->csv); 
		$reader = PHPExcel_IOFactory::createReader($inputFileType);
		$csv = $reader->load($this->config->csv);
		$sheet = $csv->getActiveSheet();
		$this->db->connect();
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_CSV . '(' . implode(',',array_keys($this->config->csv_fields)) . ') VALUES (' . implode(',',array_fill(0,count($this->config->csv_fields),'?')) . ')';
		foreach($sheet->getRowIterator() as $rowIterator){
			$row = $rowIterator->getRowIndex();
			if(!$row) continue; // Пропускаем строку заголовков колонок
			$values = array(); $col = 0; 
			foreach($this->config->csv_fields as $csvfield) $values[] = $sheet->getCellByColumnAndRow($col++,$row)->getValue();
			$this->db->execute($query,$values);
		}
		$this->db->execute('DELETE FROM ' . $this->config->dbprefix . TABLE_CSV . ' WHERE Value12="0"');
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}

	// Обновление записей в таблице
	public function update_csv(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();
		$queries = array();
		$csv_fields = array(); foreach($this->config->csv_fields as $csvfield=>$values) if($values[2]) $csv_fields[$csvfield]=$values[2];
		$where = array(); foreach($this->config->csv_joins as $key=>$value) $where[] = TABLE_XLS . '.' . $key . '=' . TABLE_URL . '.' .$value;
		$queries[] = 'REPLACE ' . $this->config->dbprefix . TABLE_CSV . '(' . implode(',', array_keys($csv_fields)) . ') SELECT ' . implode(',', array_values($csv_fields)) . ' FROM ' . $this->config->dbprefix . TABLE_XLS . ' AS ' . TABLE_XLS . ' ' . $this->config->csv_join_type . ' ' . $this->config->dbprefix . TABLE_URL . ' AS ' . TABLE_URL . ' ON ' . implode(' AND ', $where);
		$queries[] = 'DELETE FROM ' . $this->config->dbprefix . TABLE_CSV . ' WHERE Value12="0"';
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	// Экспорт записей в файл
	public function export_csv(){
		$start = microtime(true);
		set_time_limit(0);
		
		$addr = explode('/', $this->config->imagehost);
		
		// http://stackoverflow.com/questions/1987579/how-to-remove-warning-messages-in-php
		error_reporting(E_ERROR | E_PARSE);
		unlink($this->config->csv);	
		$file = fopen($this->config->csv,"w");
		// http://www.skoumal.net/en/making-utf-8-csv-excel
		//add BOM to fix UTF-8 in Excel
		fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		// The fputcsv() function formats a line as CSV and writes it to an open file.
		$headers = array(); foreach($this->config->csv_fields as $field) $headers[] = $field[1];
		fputcsv($file,$headers,';'); // Добавляем строку с заголовками колонок
		$where = array(); foreach($this->config->csv_joins as $key=>$value) $where[] = TABLE_XLS . '.' . $key . '=' . TABLE_URL . '.' .$value;
		$this->db->connect();
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_XLS . ' AS ' . TABLE_XLS . ' ' . $this->config->csv_join_type . ' ' . $this->config->dbprefix . TABLE_URL . ' AS ' . TABLE_URL . ' ON ' . implode(' AND ', $where));
		while($row=$this->db->fetch_row($result)){
			for($i = 1; $i <= 6; $i++) if($row[IMAGE . $i]) {
				$addr[count($addr) - 1] =  $row[IMAGE . $i];
				$row[IMAGE . $i] = implode('/', $addr);
			}
			$values = array(); foreach($this->config->csv_fields as $field) $values[] = $field[2]?$row[$field[2]]:'';
			// The fputcsv() function formats a line as CSV and writes it to an open file.
		  	fputcsv($file,$values,';');
		}
		$this->db->free_result($result);
		$this->db->disconnect();
		fclose($file);
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	/*
	1.	Преобразование прайса www.tursportopt.ru/price/opt.xls   в базовый формат каталога товаров
	2.	При формировании файла необходимо парсить по названию товара страницы на сайте поставщика, например: http://tursportopt.ru/category/kovea/
	3.	Скачиваем все картинки, параметры и описание
	4.	Параметры подставляем в соответсвующие столбцы в базовом файле
	5.	Картинки скачиваем на хостиг и добавляем прямую ссылку на файл в базовый excel. Не забываем про водный знак
	6.	Цена продажи = РРЦ (нужно, чтобы столбец можно было настраивать в конфиге)
	7.	Цена закупки = столбец D (нужно, чтобы столбец можно было настраивать в конфиге)
	*/
	public function task1(){
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();

		$result = $this->db->query('SELECT MAX(' . ID . ') FROM ' . $this->config->dbprefix . TABLE_TASK_QUEUE);
		$taskId = $this->db->fetch_single($result); $taskId=$taskId?$taskId+1:1;
		$this->db->free_result($result);
		$columns = array(ID,CLAZZ,METHOD);
		$query = 'REPLACE ' . $this->config->dbprefix . TABLE_TASK_QUEUE . '(' . implode(',',$columns) . ') VALUES (' . implode(',',array_fill(0,count($columns),'?')) . ')';
		$this->db->execute($query,array($taskId++,APPLICATION,'clear_xls'));
		$this->db->execute($query,array($taskId++,APPLICATION,'clear_csv'));
		$this->db->execute($query,array($taskId++,APPLICATION,'import_xls'));
		$this->db->execute($query,array($taskId++,APPLICATION,'update_csv'));
		$this->db->execute($query,array($taskId++,APPLICATION,'export_csv'));

		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}

	public function task(){
		// http://stackoverflow.com/questions/486181/php-suppress-output-within-a-function
		ob_start();
		$start = microtime(true);
		set_time_limit(0);
		$this->db->connect();

		$addr = explode('/', $this->config->imagehost);
				
		$queries = array();
		
		// Очистка временных таблиц
		foreach(array(TABLE_XLS) as $table) $queries[] = 'TRUNCATE ' . $this->config->dbprefix . $table;
		
		$result = $this->db->multi_query(implode(';',$queries));
		$this->db->free_multi_result($result);
		
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

		$tempFile = $this->config->tempcsvfilename . getmypid() . '.' . 'csv';
		// http://stackoverflow.com/questions/1987579/how-to-remove-warning-messages-in-php
		error_reporting(E_ERROR | E_PARSE);
		unlink($tempFile);	
		
		// http://stackoverflow.com/questions/16251625/how-to-create-and-download-a-csv-file-from-php-script
		$file = fopen($tempFile,"w");
		// http://www.skoumal.net/en/making-utf-8-csv-excel
		//add BOM to fix UTF-8 in Excel
		fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		// The fputcsv() function formats a line as CSV and writes it to an open file.
		$headers = array(); foreach($this->config->csv_fields as $field) $headers[] = $field[1];
		fputcsv($file,$headers,';'); // Добавляем строку с заголовками колонок
		$where = array(); foreach($this->config->csv_joins as $key=>$value) $where[] = TABLE_XLS . '.' . $key . '=' . TABLE_URL . '.' .$value;
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_XLS . ' AS ' . TABLE_XLS . ' ' . $this->config->csv_join_type . ' ' . $this->config->dbprefix . TABLE_URL . ' AS ' . TABLE_URL . ' ON ' . implode(' AND ', $where));
		while($row=$this->db->fetch_row($result)){
			for($i = 1; $i <= 6; $i++) if($row[IMAGE . $i]) {
				$addr[count($addr) - 1] =  $row[IMAGE . $i];
				$row[IMAGE . $i] = implode('/', $addr);
			}
			$values = array(); foreach($this->config->csv_fields as $field) $values[] = $field[2]?$row[$field[2]]:'';
			// The fputcsv() function formats a line as CSV and writes it to an open file.
		  	fputcsv($file,$values,';');
		}
		$this->db->free_result($result);
		$this->db->disconnect();
		fclose($file);
		
		// http://stackoverflow.com/questions/486181/php-suppress-output-within-a-function
		ob_end_clean();
		
		ob_start();
		header('Accept-Ranges: bytes');
		header('Content-Type: application/csv; charset=UTF-8');
    	header('Content-Disposition: attachement; filename="' . $this->config->csv . '"');
		header("Content-Length: " . filesize($tempFile));
		readfile($tempFile);
		ob_end_flush();

		// http://stackoverflow.com/questions/486181/php-suppress-output-within-a-function
		ob_start();
		// http://stackoverflow.com/questions/1987579/how-to-remove-warning-messages-in-php
		error_reporting(E_ERROR | E_PARSE);
		unlink($tempFile);
			
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
		// http://stackoverflow.com/questions/486181/php-suppress-output-within-a-function
		ob_end_clean();
	}
	
	public function cron(){
		$start = microtime(true);
		set_time_limit(0);

		$this->db->connect();		
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_TASK_QUEUE . ' ORDER BY ' . ID);
		$rows = $this->db->fetch_all_rows($result);
		$this->db->free_result($result);
		$this->db->disconnect();

		$query = 'DELETE FROM ' . $this->config->dbprefix . TABLE_TASK_QUEUE . ' WHERE ' . ID . '=?';
		foreach($rows as $row){
			$id = $row[ID];
			$clazz = $row[CLAZZ];
			$method = $row[METHOD];
			try
			{
				$instance = new $clazz();
				$instance->$method();
			}
			catch (Exception $e)
			{
				var_dump($e);
			}
			$values = array($id); 
			echo "<pre>$clazz $method complite.</pre>";
			$this->db->connect();		
			$this->db->execute($query,$values);
			$this->db->disconnect();
		}

		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
}