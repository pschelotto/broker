<?php
namespace App\Controller;

use App\Lib\Config;
use orm\CarritusORM;
use orm\BrokerDB\Usuario;

class ApiController extends Controller
{
	public function index($params)
	{
		$user = Usuario::findOneBy(['username'=> $params['username'], 'password' => CarritusORM::codificar($params['password'])]);

		$data = $user->toArray();
		unset($data['id']);
		unset($data['password']);
		echo json_encode($data);
	}
}