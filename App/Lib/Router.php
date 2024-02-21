<?php namespace App\Lib;

class Router
{
    public static function get($route, $callback)
    {
        if (strcasecmp($_SERVER['REQUEST_METHOD'], 'GET') !== 0) {
            return;
        }

        self::on($route, $callback);
    }

    public static function post($route, $callback)
    {
        if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') !== 0) {
            return;
        }

        self::on($route, $callback);
    }

    public static function on($regex, $cb)
    {
        $params = $_SERVER['REQUEST_URI'];
        $params = (stripos($params, "/") !== 0) ? "/" . $params : $params;
        $regex = str_replace('/', '\/', $regex);

        $is_match = preg_match('/^' . ($regex) . '$/', $params, $matches, PREG_OFFSET_CAPTURE);

        if ($is_match) {
            // first value is normally the route, lets remove it
            array_shift($matches);
            // Get the matches as parameters
            $params = array_map(function ($param) {
                return $param[0];
            }, $matches);
            $cb(new Request($params), new Response());
        }
    }
    
	public static function ResolveController($req)
	{
		$app = null;
		if(count($req->params)==2)
			list($app,$action_args) = $req->params;
		else
			list($action_args) = $req->params;


		$args = null;
		if(preg_match('/(.*?)\?(.*)/',$action_args,$match))
			list($nil,$action,$args) = $match;
		else
			$action = $action_args;

		parse_str($args,$params);
		if(isset($params['_']))
			unset($params['_']);

		if(!$app)
		{
			$app = $action;
			$action = 'index';
		}

		$class = "\\App\\Controller\\{$app}";
		if(!class_exists($class))
		{
			$app = ucfirst($app)."Controller";
			$class = "\\App\\Controller\\{$app}";

//			if(!class_exists($class))
//				throw new \Exception("El controlador \"$app\" no existe.",1);
		}

		$controller = new $class();
		$controller->$action($params,$_POST,file_get_contents('php://input'));
	}
}
