<?php
namespace App\Controller;

use App\Lib\Config;
use orm\CarritusORM;
use orm\BrokerDB\Usuario;

class ApiController extends Controller
{
	public function index($params)
	{
		if(!$user = Usuario::findOneBy(['username'=> $_POST['username'], 'password' => $_POST['password']]))
			$data = ['status' => 'error'];
		else
		{
			$data = $user->toArray();
			unset($data['id']);
			unset($data['username']);
			unset($data['password']);
			unset($data['tipo_bot']);
			unset($data['payment_at']);
			unset($data['updated_at']);
			unset($data['created_at']);
		}

		echo json_encode($data);
	}
}