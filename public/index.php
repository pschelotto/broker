<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../App/Lib/globals.php';

use App\Lib\App;
use App\Lib\Router;
use App\Lib\Request;
use App\Lib\Response;
use App\Lib\Config;


//try{

	Router::on('/([^/]+)(?:/([^/]+))?', function (Request $req, Response $res) {
		Router::ResolveController($req);
	});

	Router::get('/', function (Request $req, Response $res) {

	//	if(Config::get('backend'))
			$req->params = array('usuario','index');
	//	else
	//		$req->params = array('api_robot','index');

		Router::ResolveController($req);
	});
	/*
	Router::get('/post/([0-9]*)', function (Request $req, Response $res) {
		$res->toJSON([
			'post' =>  ['id' => $req->params[0]],
			'status' => 'ok'
		]);
	});

	*/
/*
}
catch(\Error $e)
{
	header('HTTP/1.1 503 Service Temporarily Unavailable');
	throw $e;
}*/