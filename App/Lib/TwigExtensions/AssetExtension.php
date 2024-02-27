<?php

namespace App\Lib\TwigExtensions;

class AssetExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array (
              new \Twig_SimpleFunction('asset', array('App\Lib\TwigExtensions\AssetExtension', 'asset')),
              new \Twig_SimpleFunction('path', array('App\Lib\TwigExtensions\AssetExtension', 'path')),
        );
    }

    public function getName()
    {
        return 'App:Asset';
    }

	public static function asset($str)
	{
    	$path = str_replace('index.php','',$_SERVER['SCRIPT_NAME']);
    	return str_replace('//','/',$path.$str);
    }

	public static function path($str)
	{
    	$path = str_replace('index.php','',$_SERVER['SCRIPT_NAME']);
    	$path = str_replace('public/','',$path);
    	return str_replace('//','/',$path.$str);
    }
}