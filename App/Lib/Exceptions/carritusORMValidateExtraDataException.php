<?php

namespace App\Lib\Exceptions;

class carritusORMValidateExtraDataException extends carritusORMValidateException {
	public function __construct($data = null, $code = 0, \Exception $previous = null) {
		$message = 'El dato '.var_export($data,true).' no tiene columna en la tabla';
		parent::__construct($message, $code, $previous);
	}
}