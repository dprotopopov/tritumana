<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru
require_once( dirname(__FILE__) . '/configuration.php' );
require_once( dirname(__FILE__) . '/database.php' );
require_once( dirname(__FILE__) . '/application.php' );
require_once( dirname(__FILE__) . '/functions.php' );
require_once( dirname(__FILE__) . '/defines.php' );

	// http://stackoverflow.com/questions/486181/php-suppress-output-within-a-function
	ob_start();

	$config = new JConfig();
	$db = new JDatabase();
	$app = new JApp();

	// http://stackoverflow.com/questions/486181/php-suppress-output-within-a-function
	ob_end_clean();

	$app->task();		
?>