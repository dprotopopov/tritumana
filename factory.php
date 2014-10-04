<?php
//////////////////////////////////////////////////////////////////////////////
// Разрабочик dmitry@protopopov.ru

abstract class JFactory
{
	public static $application = null;
	public static $config = null;
	public static $database = null;
	public static $insales = null;
	public static $magento = null;

	public static function getApplication()
	{
		if (!self::$application)
		{
			if (class_exists('JApplication'))
			{
				self::$application = JApplication::getInstance();
			}
			else
			{
				throw new Exception('JApplication not loaded, unable to load JApplication instance', 500);
			}
		}
		return self::$application;
	}
	public static function getConfig()
	{
		if (!self::$config)
		{
			if (class_exists('JConfig'))
			{
				self::$config = JConfig::getInstance();
			}
			else
			{
				throw new Exception('JConfig not loaded, unable to load JConfig instance', 500);
			}
		}
		return self::$config;
	}
	public static function getDbo()
	{
		if (!self::$database)
		{
			if (class_exists('JDatabase'))
			{
				self::$database = JDatabase::getInstance();
			}
			else
			{
				throw new Exception('JDatabase not loaded, unable to load JDatabase instance', 500);
			}
		}
		return self::$database;
	}
	public static function getInSales()
	{
		if (!self::$insales)
		{
			if (class_exists('InSales'))
			{
				self::$insales = InSales::getInstance();
			}
			else
			{
				throw new Exception('InSales not loaded, unable to load InSales instance', 500);
			}
		}
		return self::$insales;
	}
	public static function getMagento()
	{
		if (!self::$magento)
		{
			if (class_exists('Magento'))
			{
				self::$magento = Magento::getInstance();
			}
			else
			{
				throw new Exception('Magento not loaded, unable to load Magento instance', 500);
			}
		}
		return self::$magento;
	}
}

