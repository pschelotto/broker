<?php
namespace App\Controller;

use App\Lib\Config;
use orm\BrokerDB\Usuario;

class UsuarioController extends AdminController
{
	public function index($params)
	{
		$usuarios = Usuario::findAll();

		return $this->render('usuario/list.html.twig',[
			'usuarios' => $usuarios,
		]);		
	}

	public function new($params)
	{
		$form = $this->getForm(true);

		if($_POST)
		{

			if($object = $this->bind($form, $_POST, '\orm\BrokerDB\Usuario'))
			{
				$object->save();
				$this->redirect(preg_replace('/[^\/]+$/','',$_SERVER['SCRIPT_URI']??'/'));
			}
		}

		return $this->render('usuario/edit.html.twig',[
			'form' => $form,
		]);
	}

	public function edit($params)
	{
		$form = $this->getForm(false);

		$object = Usuario::find($params['id']);

		if($_POST)
		{
			if($object = $this->bind($form, $_POST, $object))
			{
				$object->save();
				$this->redirect(preg_replace('/[^\/]+$/','',$_SERVER['SCRIPT_URI']??'/'));
			}
		}

		return $this->render('usuario/edit.html.twig',[
			'form' => $form,
			'object' => $object,
		]);		
	}
	
	protected function getForm($is_new)
	{
		$form = array(
			'Acceso' => array(
				'username' => array('label' => 'Usuario'),
				'password' => array('label' => 'Password', 'type' => 'password'),
				'tipo_bot' => array('label' => 'Tipo de Bot'),
			),
			'Broker' => array(
				'broker_account' => array('label' => 'Cuenta'),
				'broker_server' => array('label' => 'Servidor'),
			),
			'Configuración' =>array(
				'telegram' => array('label' => 'Telegram', 'type' => 'checkbox', 'options' => ['switch' => true]),
			),
			'Fechas' => array(
				'payment_at' => array('label' => 'Último pago', 'type' => 'date'),
				'expires_at' => array('label' => 'Expira el día', 'type' => 'date'),
			),
		);
		
		if(!$is_new)
		{
			$form['Fechas']['created_at'] = array('label' => 'Creado', 'type' => 'datetime-local', 'attrs' => ['disabled'=>'']);
			$form['Fechas']['updated_at'] = array('label' => 'Actualizado', 'type' => 'datetime-local', 'attrs' => ['disabled'=>'']);
		}

		return $form;
	}
	
	protected function bind($form,$post_data,$object)
	{
		// Para setear las checkbox:
		foreach($form as $section)
			foreach($section as $field => $config)
				if(is_array($config) && ($config['type']??null)=='checkbox')
					$post_data[$field] = isset($post_data[$field])?1:0;

		if(is_string($object))
			$object = new $object();

		$object->updateData($post_data);
		return $object;
	}

	public function delete($params)
	{
		$object = Usuario::find($params['id']);
		$object->delete();
		$this->redirect(preg_replace('/[^\/]+$/','',$_SERVER['SCRIPT_URI']??'/'));
	}
}