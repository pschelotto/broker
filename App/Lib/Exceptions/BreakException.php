<?php

namespace App\Lib\Exceptions;

class BreakException extends \Error
{
	protected $data;

    public function __construct($message="", $code=0, \Exception $previous=NULL, $data = NULL)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    public function getData()
    {
        return $this->data;
    }
}
