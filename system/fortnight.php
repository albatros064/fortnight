<?php

require_once "error.php";

require_once "base.php";

require_once "controller.php";
require_once "model.php";
require_once "plugin.php";
require_once "helper.php";
require_once "template.php";

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
				if (!isset($input['all' ][$name]) ) {
					$input['all' ][$name] = $value;
				}
				if (!isset($input['vars'][$name]) ) {
					$input['vars'][$name] = $value;
				}
			}
			$request = substr($request, 0, strrpos(substr($request, 0, strpos($request, ":") ), "/") );
		}
		// Parse $_POST
		if (isset($_POST) && !empty($_POST) ) {
			foreach ($_POST as $name => $value) {
				if (!isset($input['all' ][$name]) ) {
					$input['all' ][$name] = $value;
				}
				if (!isset($input['post'][$name]) ) {
					$input['post'][$name] = $value;
				}
			}
		}

		// Start up Plugin Manager
		$plugin_manager = new FN_Plugin_Manager($this->config['global']);
			
		// Load admin plugins
		$admin_plugins = $plugin_manager->get_plugins('system');
		
		foreach ($admin_plugins as $admin_plugin) {
			$plugin_manager->load_plugin($admin_plugin);
		}

		$user_plugins = $plugin_manager->get_plugins('');
		foreach ($user_plugins as $user_plugin) {
			$plugin_manager->load_plugin($user_plugin);
		}
			
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

		$template_manager = NULL;
		
		if (empty($uri_prefix) ) {
			$template_manager = new FN_Template;
			$templated_page = $template_manager->load($request);

			if ($templated_page !== FALSE) {
				$request = $templated_page['request'];
			}

			$path_prefix = 'application';
		}
		
		if (!empty($uri_prefix) ) {
			$request = "/" . trim(str_replace($uri_prefix, "", $request), "/");
		}
		
		// Match request to route
		$page_route = null;
		
		foreach ($this->config['routes'] as $pattern => $route) {
			if (preg_match($pattern, $request, $match) ) {
				foreach ($route as $index => $part) {
					if (is_integer($part) ) {
						$route[$index] = $match[$part];
					}
				}

				$o = strrpos($route['controller'], "/");

				if ($o !== FALSE) {
					$route['path'] .= $path_prefix = substr($route['controller'], 0, $o);
					$route['controller'] = substr($route['controller'], $o + 1);
				}

				$route['file_prefix'] = $path_prefix;
				$route['prefix'] = $uri_prefix;
				
				$page_route = $route;
				break;
			}
		}

		// Load request controller
		$controller = $this->load_controller($page_route);

		// Execute request
		if (method_exists($controller, $page_route['method']) ) {
			$method = $page_route['method'];

			// Set the controller's $request
			$controller->request = array(
				'route'  => $page_route,
				'input'  => $input
			);
			if ($template_manager && $template_manager->loaded() ) {
				$controller->template = $template_manager;
			}
			$controller->$method();
		}
		else {
			echo "Do the ol' 404...";
		}
	}

	protected function load_controller($request) {

		// Check for controller class file
		$controller_file = trim(strtolower($request['controller']), "/") . ".php";
		$controller_path = $this->config['global']['path']['absolute'] . $request['file_prefix'] . "/controller" . $request['path'];

		$controller_file = $controller_path . $controller_file;

		if (file_exists($controller_file) ) {
			ob_start();
			include $controller_file;
			ob_clean();
		}
		else {
			#throw new Exception($controller_file);
			return NULL;
		}

		// Check for controller class and instanciate
		$controller_class = ucfirst($request['controller']) . "_Controller";

		if (class_exists($controller_class) ) {
			return new $controller_class;
		}
		return NULL;
	}
}

?>
