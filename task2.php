<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru

require_once( dirname(__FILE__) . '/configuration.php' );
require_once( dirname(__FILE__) . '/insales.php' );
require_once( dirname(__FILE__) . '/magento.php' );
require_once( dirname(__FILE__) . '/factory.php' );

	$config = JFactory::getConfig();
	$insales = JFactory::getInSales();
	$magento = JFactory::getMagento();
	if($config->insales_enabled) $insales->task2();		
	if($config->magento_enabled) $magento->task2();		
