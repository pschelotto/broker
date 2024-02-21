<?php

namespace App\Lib\Exceptions;

class carritusORMInvalidDataException extends carritusORMValidateException {
	public function __construct($column, $rule, $data = null, $code = 0, \Exception $previous = null) {
		$message = $rule." ".var_export($data,true)." en columna $column";
		parent::__construct($message, $code, $previous);
	}
}
