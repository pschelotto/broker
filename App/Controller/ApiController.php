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
		unset($data['username']);
		unset($data['password']);
		unset($data['tipo_bot']);
		unset($data['payment_at']);
		unset($data['updated_at']);
		unset($data['created_at']);
		echo json_encode($data);
	}
}