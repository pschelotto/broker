<?php

namespace orm\BrokerDB;

class Usuario extends BrokerDB {

	static $create_table_str = "
CREATE TABLE `usuario` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `broker_account` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `broker_server` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `telegram` tinyint,
  `payment_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_at_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;	
	";

	static $columns = array(
		'id' => array(
			'type' => 'integer',
			'length' => '20'
		),
		'username' => array(
			'type' => 'string',
			'length' => '255'
		),
		'password' => array(
			'type' => 'password',
			'length' => '255',
		),
		'broker_account' => array(
			'type' => 'string',
			'length' => '255',
		),
		'broker_server' => array(
			'type' => 'string',
			'length' => '255',
		),
		'telegram' => array(
			'type' => 'integer',
			'default' => '0'
		),
		'payment_at' => array(
			'type' => 'date',
			'format' => 'Y-m-d',
			'default' => null
		),
		'created_at' => array(
			'type' => 'timestamp',
			'format' => 'Y-m-d H:i:s',
		),
	);
	
	public function checkPassword($pass){
		return $this->getPassword()==CarritusORM::codificar($pass);
	}
}
