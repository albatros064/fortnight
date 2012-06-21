<?php

require_once "error.php";

require_once "base.php";

require_once "controller.php";
require_once "model.php";
require_once "plugin.php";
require_once "helper.php";

class Fortnight extends FN_Base {
	function __construct() {
		parent::__construct();
		$this->config['routes'] = $this->_load_config_file('routes');
	}
	
	function execute() {
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
		if (isset($_GET) && !empty($_GET) ) {
			foreach ($_GET as $name => $value) {
				if (!isset($input['all'][$name]) )
					$input['all'][$name] = $value;
				if (!isset($input['get'][$name]) )
					$input['get'][$name] = $value;
			}
		}
		// Parse URI variables
		if(preg_match_all('#([^/]+):([^/]+)#', $request, $match)) {
			foreach($match[1] as $index => $name) {
				$value = $match[2][$index]; 
				if (!isset($input['all' ][$name]) )
					$input['all' ][$name] = $value;
				if (!isset($input['vars'][$name]) )
					$input['vars'][$name] = $value;
			}
			$request = substr($request, 0, strrpos(substr($request, 0, strpos($request, ":") ), "/") );
		}
		// Parse $_POST
		if (isset($_POST) && !empty($_POST) ) {
			foreach ($_POST as $name => $value) {
				if (!isset($input['all' ][$name]) )
					$input['all' ][$name] = $value;
				if (!isset($input['post'][$name]) )
					$input['post'][$name] = $value;
			}
		}

		// Start up Plugin Manager
		$plugin_manager = new FN_Plugin_Manager($this->config['global']);
			
		// Load admin plugins
		$admin_plugins = $plugin_manager->get_plugins('system');
		
		foreach ($admin_plugins as $admin_plugin)
			$plugin_manager->load_plugin($admin_plugin);
			
		#pr($this->plugin_manager->loaded);
		pr($plugin_manager->assignments);
		
		$uri_prefix = '';
		$path_prefix = '';
		
		// Match request to plugin-registered paths
		$plugin_paths = $plugin_manager->get_web_paths();
		foreach ($plugin_paths as $path => $details) {
			$path = "/" . trim($path, "/");
			$pos = strpos($request, $path);
			if ($pos !== FALSE && $pos == 0) {
				if (strlen($path) > $uri_prefix) {
					$uri_prefix = $path;
					$path_prefix = $details['path'];
				}
			}
		}

		$this->_set_plugin_manager($plugin_manager);
		
		if (empty($uri_prefix) ) {
			// Load registered routes
			
		}
		
		debug_out($uri_prefix);
		debug_out($path_prefix);
		
		if (!empty($uri_prefix) )
			$request = "/" . trim(str_replace($uri_prefix, "", $request), "/");
		pr($input);		
		pr($request);
		
		// Match request to route
		$possible_routes = array();
		
		foreach ($this->config['routes'] as $pattern => $route) {
			pr($pattern);
			if (preg_match($pattern, $request, $match) ) {
				pr("match");
				foreach ($route as $index => $part) {
					if (is_integer($part) )
						$route[$index] = $match[$part];
				}
				$o = strrpos($route['controller'], "/");
				#$route['path'] = '/';
				if ($o !== FALSE) {
					$route['path'] .= $path_prefix = substr($route['controller'], 0, $o);
					$route['controller'] = substr($route['controller'], $o + 1);
				}
				
				$possible_routes[] = $route;
			}
		}
		
		// Load request controller
		pr($possible_routes);

		#$db = new Db_Helper();
		$this->load_helper("Db");
		
		$result = $this->Db->query("SELECT * FROM fn_user");
		while ($res = $this->Db->get_row() ) {
			pr($res);
		}
		
		// Execute request
		pr("request executed");
	}
}

?>
