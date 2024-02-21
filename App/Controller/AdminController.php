<?php

namespace App\Controller;

use App\Lib\Config;

class AdminController extends Controller
{
	// https://adminlte.io/themes/v3/iframe.html?#
	public function render($template,$params)
	{

		return parent::render($template,$params);
	}

	public function generateLabels($obj)
	{
		foreach(array_keys($obj) as $item)
			$ret[$item] = str_replace('_',' ',str_replace('_id','',ucfirst($item)));

		return $ret;
	}
}
