<?php
namespace App\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Lib\Config;
use Twig\Extra\Intl\IntlExtension;

class Controller
{
	public function __construct()
	{
		ini_set('memory_limit',-1);

		if(strstr($_SERVER['APPLICATION_ENV']??null,'backend')===false)
//				throw new \Exception("Path not found");
			exit;

		$loader = new FilesystemLoader('../templates');
		$twig = new Environment($loader,array(
			'debug' => true,
		));
		$twig->addExtension(new \App\Lib\TwigExtensions\DebugExtension());
		$twig->addExtension(new IntlExtension());

		set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext){
			$this->errors[] = array(
				'errno' => $errno,
				'errstr' => $errstr,
				'errfile' => $errfile,
				'errline' => $errline,
				'errcontext' => $errcontext,
			);
		});

		$this->twig = $twig;
	}

	public function render($template,$params)
	{
		parse_str($_SERVER['QUERY_STRING'],$parse_str);

		if(!empty($this->flash_bag))
		{
			$flash = $this->flash_bag;
//			unset($this->flash_bag);
		}

		$params['app'] = array(
			'request' => array(
				'uri' => $_SERVER['REQUEST_URI'],
				'get' => $parse_str,
			),
			'server' => $_SERVER,
			'config' => Config::getAllConfig(),
			'flash' => $flash??null,
		);
		$params['errors'] = $this->errors??[];

		echo $this->twig->render($template,$params);
	}

	public function redirect($url)
	{
		header("Location: $url");
		exit;
	}

	public function addFlash($type,$message)
	{
		$this->flash_bag[$type][] = $message;
	}

	protected function sanitize($str)
	{
		return trim(str_replace("'","\\'",html_entity_decode($str)));
	}
}
