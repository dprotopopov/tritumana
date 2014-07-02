<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru

/** Include PHPExcel */
require_once dirname(__FILE__) . '/PHPExcel_1.8.0_doc/Classes/PHPExcel.php';

require_once( dirname(__FILE__) . '/configuration.php' );
require_once( dirname(__FILE__) . '/database.php' );
require_once( dirname(__FILE__) . '/application.php' );
require_once( dirname(__FILE__) . '/functions.php' );
require_once( dirname(__FILE__) . '/defines.php' );


class JApp {
	private $config;
	private $db;
	
	public function __construct() {
		$this->config = new JConfig();
		$this->db = new JDatabase();
	}
	
	public function rebuild_database(){
		$start = microtime(true);
		set_time_limit(0);
		$xlscolumns = array(); foreach($this->config->xlsfields as $field=>$values) $xlscolumns[] = $field . ' ' . $values[0];
		$urlcolumns = array(); foreach($this->config->urlfields as $field=>$values) $urlcolumns[] = $field . ' ' . $values[0];
		$this->db->connect();
		$this->db->query('DROP TABLE IF EXISTS ' . $this->config->dbprefix . TABLE_XLS);
		$this->db->query('DROP TABLE IF EXISTS ' . $this->config->dbprefix . TABLE_URL);
		$this->db->query('DROP TABLE IF EXISTS ' . $this->config->dbprefix . TABLE_PAGE);
		$this->db->query('DROP TABLE IF EXISTS ' . $this->config->dbprefix . TABLE_IMAGE);
		$this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->config->dbprefix . TABLE_XLS . '(' . implode(',', $xlscolumns) . ', PRIMARY KEY (' . implode(',', $this->config->xlskeys) . '), INDEX (' . implode(',', array_keys($this->config->joins)) . '))');
		$this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->config->dbprefix . TABLE_URL . '(' . implode(',', $urlcolumns) . ', PRIMARY KEY (' . implode(',', $this->config->urlkeys) . '), INDEX (' . implode(',', array_values($this->config->joins)) . '))');
		$this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->config->dbprefix . TABLE_PAGE . '(' . FIELD_URL . ' varchar(255),' . FIELD_LOADED . ' integer, PRIMARY KEY (' . FIELD_URL . '))');
		$this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->config->dbprefix . TABLE_IMAGE . '(' . FIELD_URL . ' varchar(255),' . FIELD_FILE . ' varchar(255),' . FIELD_LOADED . ' integer, PRIMARY KEY (' . FIELD_FILE . '))');
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function info(){
		set_time_limit(0);
		$this->db->connect();
		$xlscolumns = array(); foreach($this->config->xlsfields as $field=>$values) $xlscolumns[] = $field . ' ' . $values[0];
		$urlcolumns = array(); foreach($this->config->urlfields as $field=>$values) $urlcolumns[] = $field . ' ' . $values[0];
		$this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->config->dbprefix . TABLE_XLS . '(' . implode(',', $xlscolumns) . ', PRIMARY KEY (' . implode(',', $this->config->xlskeys) . '), INDEX (' . implode(',', array_keys($this->config->joins)) . '))');
		$this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->config->dbprefix . TABLE_URL . '(' . implode(',', $urlcolumns) . ', PRIMARY KEY (' . implode(',', $this->config->urlkeys) . '), INDEX (' . implode(',', array_values($this->config->joins)) . '))');
		$this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->config->dbprefix . TABLE_PAGE . '(' . FIELD_URL . ' varchar(255),' . FIELD_LOADED . ' integer, PRIMARY KEY (' . FIELD_URL . '))');
		$this->db->query('CREATE TABLE IF NOT EXISTS ' . $this->config->dbprefix . TABLE_IMAGE . '(' . FIELD_URL . ' varchar(255),' . FIELD_FILE . ' varchar(255),' . FIELD_LOADED . ' integer, PRIMARY KEY (' . FIELD_FILE . '))');

		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE loaded="0"');
		$queue = $this->db->fetch_single($result);
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_IMAGE);
		$queue_total = $this->db->fetch_single($result);
		echo "<pre>Image queue: <b>$queue/$queue_total</b></pre>";
		
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_LOADED . '<' . (time()-$this->config->pageupdatetime));
		$queue = $this->db->fetch_single($result);
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_PAGE);
		$queue_total = $this->db->fetch_single($result);
		echo "<pre>Page queue: <b>$queue/$queue_total</b></pre>";
		
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_URL );
		$count = $this->db->fetch_single($result);
		echo "<pre>Url records downloaded: <b>$count</b></pre>";
		
		$result = $this->db->query('SELECT COUNT(*) FROM ' . $this->config->dbprefix . TABLE_XLS );
		$count = $this->db->fetch_single($result);
		echo "<pre>Xls records downloaded: <b>$count</b></pre>";
		$this->db->disconnect();
	}
	
	public function page_curl_cron(){
		$start = microtime(true);
		set_time_limit(0);
		$default = parse_url($this->config->url);	
		$this->db->connect();
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE NOT ' . FIELD_URL . ' LIKE "%' . $default['host'] . '%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.jpg%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.jpeg%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.gif%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.png%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.pdf%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.doc%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.xls%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.ppt%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.docx%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.xlsx%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.pptx%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.avi%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.mov%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.mpg%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.mpeg%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.swf%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.exe%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.msi%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.zip%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.swf%"');
		$this->db->query('INSERT IGNORE ' . $this->config->dbprefix . TABLE_PAGE . '(' . FIELD_URL . ',' . FIELD_LOADED . ') VALUES ("' . safe($this->config->url) . '",0)');
		$records = array(); 
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_LOADED . '<' . (time()-$this->config->pageupdatetime) .' AND ' . FIELD_URL . ' LIKE "%product%" ORDER BY ' . FIELD_LOADED . ' LIMIT ' . ($this->config->pagecronlimit - count($records)));
		while($row=$this->db->fetch_row($result)) $records[]=$row[FIELD_URL];
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_LOADED . '<' . (time()-$this->config->pageupdatetime) .' AND NOT ' . FIELD_URL . ' LIKE "%product%" ORDER BY ' . FIELD_LOADED . ' LIMIT ' . ($this->config->pagecronlimit - count($records)));
		while($row=$this->db->fetch_row($result)) $records[]=$row[FIELD_URL];
		foreach($records as $url){
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
				$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_PAGE . ' WHERE ' . FIELD_URL . '="' . safe($url) . '"');
				// $pid === -1 failed to fork
				// $pid == 0, this is the child thread
				// $pid != 0, this is the parent thread
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
			$elements = $xpath->query('//a[@href]//@href');
			if (!is_null($elements)) {
				foreach ($elements as $element) {
					$parse = parse_url($element->nodeValue);
					if(isset($parse['fragment'])) unset($parse['fragment']);
					if(isset($parse['query'])) unset($parse['query']);
					$this->db->query('INSERT IGNORE ' . $this->config->dbprefix . TABLE_PAGE . '(' . FIELD_URL . ',' . FIELD_LOADED . ') VALUES ("' . safe(unparse_url($parse,$default)) . '",0)');
				}
			}
			
			// Обрабатываем поля на странице
			$fields = array();
			foreach($this->config->urlfields as $urlfield=>$values){
				$elements = $xpath->query($values[1]);
				$tokens = array();
				if (!is_null($elements)) {
					foreach ($elements as $element) $tokens[] = preg_replace($values[2], $values[3], $element->nodeValue);
				}
				$fields[$urlfield] = safe(trim(implode('',$tokens)));
			}

			// Обрабатываем транслит изображений
			for($i=1;$i<=6;$i++){
				$src = $fields["image" . $i];
				if($src){
					$imageUrl = unparse_url(parse_url($src),$default);
					$type = explode(".", $imageUrl);
					$ext = strtolower($type[count($type)-1]);
					$file = $this->config->imagedir . $fields["translit"] . '_' . $i . '.' . $ext;
					$fields["image" . $i] = '/' . $file;
					$this->db->query('INSERT IGNORE ' . $this->config->dbprefix . TABLE_IMAGE . '(' . FIELD_URL . ',' . FIELD_FILE . ',' . FIELD_LOADED . ') VALUES ("' . safe($imageUrl) . '","' . safe($file) . '",0)');
				}
			}
			
			$this->db->query('REPLACE ' . $this->config->dbprefix . TABLE_URL . '(' . implode(',', array_keys($fields)) . ') VALUES ("' . implode('","', array_values($fields)) . '")');
			$this->db->query('REPLACE ' . $this->config->dbprefix . TABLE_PAGE . '(' . FIELD_URL . ',' . FIELD_LOADED . ') VALUES ("' . safe($url) . '",' . time() . ')');
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
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.html%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.htm%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.php%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.asp%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.asx%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.exe%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.msi%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.zip%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.mov%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.avi%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.mpg%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.mpeg%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.doc%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.xls%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.ppt%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.docx%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.xlsx%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.pptx%"');
		$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . ' LIKE "%.pdf%"');
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE loaded="0" LIMIT ' . $this->config->imagecronlimit);
		$records = array(); while($row=$this->db->fetch_row($result)) $records[$row[FIELD_FILE]]=$row[FIELD_URL];
		foreach($records as $file=>$url){
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
			$tempFile = $this->config->imagetempfilename . getmypid() . '.' . $ext;
			// http://stackoverflow.com/questions/1987579/how-to-remove-warning-messages-in-php
			error_reporting(E_ERROR | E_PARSE);
			unlink($tempFile);	
			// Загрузка и сохранение файла на диске
			$image = file_get_contents($url);
			if(!$image) {
				// Исклучаем из дальнейшей загрузки отсутствующие страницы
				$this->db->query('DELETE FROM ' . $this->config->dbprefix . TABLE_IMAGE . ' WHERE ' . FIELD_URL . '="' . safe($url) . '" AND ' . FIELD_FILE . '="' . safe($file) . '"');
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
			$func = "image".$ext;
			$func($output, $file); 

			$this->db->query('REPLACE ' . $this->config->dbprefix . TABLE_IMAGE . '(' . FIELD_URL . ',' . FIELD_FILE . ',' . FIELD_LOADED . ') VALUES ("' . safe($url) . '","' . safe($file) . '",' . time() . ')');
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
	
	public function clear_xls(){
		$start = microtime(true);
		set_time_limit(0);
		$config = new JConfig();
		$db = new JDatabase();
		$this->db->connect();
		$this->db->query('TRUNCATE ' . $this->config->dbprefix . TABLE_XLS);
		$this->db->disconnect();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function clear_url(){
		$start = microtime(true);
		set_time_limit(0);
		$config = new JConfig();
		$db = new JDatabase();
		$this->db->connect();
		$this->db->query('TRUNCATE ' . $this->config->dbprefix . TABLE_URL);
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
	
	public function import_xls(){
		$start = microtime(true);
		set_time_limit(0);
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
		$outline = array(1=>1,2=>1,3=>1,4=>1,5=>1);
		$this->db->connect();
		foreach($sheet->getRowIterator() as $rowIterator){
			$row = $rowIterator->getRowIndex();
			$outline[$sheet->getRowDimension($row)->getOutlineLevel()]=$row;
			$fields = array(); foreach($this->config->xlsfields as $xlsfield=>$values) $fields[$xlsfield] = safe(trim(eval($values[1])));
			$this->db->query('REPLACE ' . $this->config->dbprefix . TABLE_XLS . '(' . implode(',',array_keys($fields)) . ') VALUES ("' . implode('","',array_values($fields)) . '")');
		}
		$this->db->disconnect();
		// http://stackoverflow.com/questions/1987579/how-to-remove-warning-messages-in-php
		error_reporting(E_ERROR | E_PARSE);
		unlink($tempFile);	
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function export_csv(){
		$start = microtime(true);
		set_time_limit(0);
		// http://stackoverflow.com/questions/1987579/how-to-remove-warning-messages-in-php
		error_reporting(E_ERROR | E_PARSE);
		unlink($this->config->csv);	
		$file = fopen($this->config->csv,"w");
		// http://www.skoumal.net/en/making-utf-8-csv-excel
		//add BOM to fix UTF-8 in Excel
		fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		// The fputcsv() function formats a line as CSV and writes it to an open file.
		fputcsv($file,array_keys($this->config->csvfields),';');
		$this->db->connect();
		$where = array(); foreach($this->config->joins as $key=>$value) $where[] = TABLE_XLS . '.' . $key . '=' . TABLE_URL . '.' .$value;
		$result = $this->db->query('SELECT * FROM ' . $this->config->dbprefix . TABLE_XLS . ' AS ' . TABLE_XLS . ' LEFT JOIN ' . $this->config->dbprefix . TABLE_URL . ' AS ' . TABLE_URL . ' ON ' . implode(' AND ', $where));
		while($row=$this->db->fetch_row($result)){
			$values = array(); foreach($this->config->csvfields as $field) $values[] = ($field&&$row[$field])?$row[$field]:'';
			// The fputcsv() function formats a line as CSV and writes it to an open file.
		  	fputcsv($file,$values,';');
		}
		$this->db->disconnect();
		fclose($file);
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
	
	public function task(){
		$start = microtime(true);
		set_time_limit(0);
		$this->clear_xls();
		$this->import_xls();
		$this->export_csv();
		$duration = microtime(true) - $start;
		echo "<pre>Execution time: <b>$duration</b> sec.</pre>";
	}
}