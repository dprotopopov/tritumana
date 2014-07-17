<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru
require_once( dirname(__FILE__) . '/application.php' );

	// http://stackoverflow.com/questions/486181/php-suppress-output-within-a-function
	ob_start();

	$app = new JApp();

	// http://stackoverflow.com/questions/486181/php-suppress-output-within-a-function
	ob_end_clean();

	$app->task();		
