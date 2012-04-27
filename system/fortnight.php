<?php

require_once "error.php";

require_once "base.php";

require_once "controller.php";
require_once "model.php";
require_once "plugin.php";
require_once "helper.php";

class Fortnight extends FN_Base
{
	function __construct($global_config)
	{
		$this->config = array(
			'global' => $global_config
		);
	}
	
	function execute()
	{
		$input = array(
			'all'  => array(),
			'get'  => array(),
			'vars' => array(),
			'post' => array(),
		);
		
		// Strip query string (will be processed through $_GET later)
		$request = explode("?", $_SERVER['REQUEST_URI']);
		$request = "/".trim(str_replace($this->config['global']['path']['base'], "", $request[0]), "/");
		
		// Parse $_GET
		if (isset($_GET) && !empty($_GET) )
		{
			foreach ($_GET as $name => $value)
			{
				if (!isset($input['all'][$name]) )
					$input['all'][$name] = $value;
				if (!isset($input['get'][$name]) )
					$input['get'][$name] = $value;
			}
			unset($_GET);
		}
		// Parse URI variables
		if(preg_match_all('#([^/]+):([^/]+)#', $request, $match))
		{
			foreach($match[1] as $index => $name)
			{
				$value = $match[2][$index]; 
				if (!isset($input['all' ][$name]) )
					$input['all' ][$name] = $value;
				if (!isset($input['vars'][$name]) )
					$input['vars'][$name] = $value;
			}
			$request = substr($request, 0, strrpos(substr($request, 0, strpos($request, ":") ), "/") );
		}
		// Parse $_POST
		if (isset($_POST) && !empty($_POST) )
		{
			foreach ($_POST as $name => $value)
			{
				if (!isset($input['all' ][$name]) )
					$input['all' ][$name] = $value;
				if (!isset($input['post'][$name]) )
					$input['post'][$name] = $value;
			}
			unset($_POST);
		}
			
		// Start up Plugin Manager
		$this->plugin_manager = new FN_Plugin_Manager($this->config['global']);
			
		// Load admin plugins
		$admin_plugins = $this->plugin_manager->get_plugins('system');
		pr($admin_plugins);
		
		// Load registered routes
		
		// Match request to route
			
		include "config/routes.php";
		
		foreach ($routes_config as $pattern => $route)
		{
			if (preg_match($pattern, $request, $match) )
			{
				foreach ($route as $index => $part)
				{
					if (is_integer($part) )
						$route[$index] = $match[$part];
				}
				$o = strrpos($route['controller'], "/");
				$route['path'] = '/';
				if ($o !== FALSE)
				{
					$route['path'] .= substr($route['controller'], 0, $o);
					$route['controller'] = substr($route['controller'], $o + 1);
				}
			}
		}
		
		// Load request controller
		
		// Execute request
		debug_out("request executed");
	}
}

?>