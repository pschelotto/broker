<?php

namespace orm\BrokerDB;

use orm\carritusORM;
use App\Lib\Config;

class BrokerDB extends carritusORM
{
	static $dbh;

	protected static function get_dbh() {

		if (!isset(self::$dbh) || (isset(self::$dbh) && self::$dbh == null)) {

			$dsn = Config::get('db_mysql');$user = Config::get('db_username');$pass = Config::get('db_password');
			if(empty($dsn) || empty($user)) {
				throw new \Exception(sprintf('Undefined params in config.php (dsn: %s, user: %s or pass)',$dsn,$user));
			}

			self::$dbh = new \PDO($dsn, $user, $pass, array(\PDO::ATTR_PERSISTENT => true));
			self::$dbh->query('SET NAMES "utf8"');
			self::$dbh->query('SET CHARACTER SET utf8');
		}

		return self::$dbh;
	}
}
