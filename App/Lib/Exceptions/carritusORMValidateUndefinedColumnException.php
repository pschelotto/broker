<?php

namespace App\Lib\Exceptions;

class carritusORMValidateUndefinedColumnException extends carritusORMValidateException {
	public function __construct($column = null, $code = 0, \Exception $previous = null) {
		$message = 'La columna '.$column.' no puede estar vacia';
		parent::__construct($message, $code, $previous);
	}
}
