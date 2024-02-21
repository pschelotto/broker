<?php

namespace App\Command;

class Command {

	var $options_def = array();

	public function __construct($info)
	{
		extract($info);

		foreach(array_keys($this->options_def) as $i => $option)
		{
			if(!isset($options[$option]))
			{
				if(isset($arguments[$i]))
				{
					$options[$option] = $arguments[$i];
					unset($arguments[$i]);
				}
				else
					$options[$option] = $this->options_def[$option]['default']??null;
			}

			if($options[$option]==='false')
				$options[$option] = false;
		}

		$this->options = $options??array();
		$this->arguments = $arguments??array();
	}

	static public function run($argc,$argv)
	{
		$cmds = json_decode(json_encode(self::getCommands(__DIR__)));
		if($argc<2)
			return self::showCmdList($cmds);
	
		$aliases = array();
		foreach($cmds as $cmd)
		{
			$name = $cmd->name;
			if(preg_match('/(.*?)\:(.*)/',$cmd->name,$match))
				$name = $match[2];

			$aliases[strtolower($name)] = $cmd->name;
			foreach($cmd->aliases as $alias)
				$aliases[$alias] = $cmd->name;
		}

		$action = strtolower($argv[1]);
		$action = $aliases[$action]??$action;
		$action = str_replace(':','\\',$action);

		$params = $args = [];
		for($i=2;$i<$argc;$i++)
		{
			$param = $argv[$i];
			if(preg_match('/^--(.*?)=(.*)/',$param,$match))
				$params[$match[1]] = $match[2];
			else
				$args[] = $param;
		}

		$class = "App\\Command\\{$action}";
		$class::launch($params,$args);
	}

	static public function launch($params,$args=array())
	{
		$class = get_called_class();
		$command = new $class(array(
			'options'   => $params, 
			'arguments' => $args,
		));

		$class::testParameters($class, $command, $params);

		try{
			$command->execute();
		} catch(\Exception $e){
			pre("\033[1;37m\033[41m Exception: \033[0m \033[0;0m\033[31m{$e->getMessage()}\033[0m");
			pre($e->getTraceAsString(),1);
		}
	}

	static protected function getCommands($dir)
	{
		$cmds = array();

		$d = dir($dir);
		while(false !== ($entry = $d->read()))
		{
			if($entry[0]=='.')
				continue;
			if(is_dir("$dir/$entry"))
			{
				$cmds = array_merge($cmds,self::getCommands("$dir/$entry"));
			}
			elseif($class_name = preg_match_1('/(.*?)\.php$/',$entry))
			{
				$r = exec("grep -m1 namespace $dir/$entry");

				$namespace = preg_match_1('/namespace (.*?);/',$r);
				$class = "\\$namespace\\$class_name";
				$cmd = new $class(array());

				$rel_dir = preg_match_1('/Command\\\(.*)/',$namespace);

				$cmds[] = array(
					'name'    => ($rel_dir?"{$rel_dir}:":"").$class_name,
					'aliases' => defined("$class::aliases")?$class::aliases:[],
					'options' => $cmd->options_def,
				);
			}
		}
		$d->close();

		return $cmds;		
	}

	static protected function showCmdList($cmds)
	{
		pre("Command list:");

		foreach($cmds as $cmd)
		{
			if(preg_match('/(.*?)\:(.*)/',$cmd->name,$match))
			{
				$cmd->name = $match[2];
				$group[$match[1]][] = $cmd;
			}
			else
				$group[""][] = $cmd;
		}
		
		ksort($group);

		foreach($group as $grp => $cmds)
		{
			if($grp)
				pre("\n$grp:");
			foreach($cmds as $cmd)
			{
				$aliases = implode(',',$cmd->aliases);
				pre(sprintf("   %-45s\t %s",$cmd->name,$aliases?"[$aliases]":""));
			}
		}
	}
	
	static protected function testParameters($action, $command, $params)
	{
		$command = json_decode(json_encode($command));

		$ok = true;
		foreach($command->options_def as $param => $opt)
			if( empty($command->options->$param) && ($opt->required??false) )
				$ok = false;

		foreach($params as $param => $value)
			if(!isset($command->options_def->$param))
				$ok = false;

		if(!$ok)
		{
			pre("$action:");
			foreach($command->options_def as $param => $opt)
			{
				if($opt->required??false)
					pre("\t\033[31m$param\033[0m: $opt->description");
				else
					pre("\t$param: $opt->description");
			}
			
			exit;
		}
	}
}
