<?php namespace App\Lib;

class Config
{
	private static $config;

	public static function get($key, $default = null)
	{
		if (is_null(self::$config)) {
			self::$config = require_once(__DIR__.'/../../config.php');
		}

		if($key=='backend' && ($_SERVER['APPLICATION_ENV']??null) == 'robotapi')
			return false;

		return !empty(self::$config[$key])?self::$config[$key]:$default;
	}

	public static function getAllConfig()
	{
		return self::$config;
	}
}