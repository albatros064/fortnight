<?php

class FN_Plugin extends FN_Base {
}

class FN_Plugin_Manager extends FN_Base {
	function __construct($global_config) {
		parent::__construct();
		$this->config = array(
			'global' => $global_config
		);
		$this->loaded = array();
		$this->assignments = array(
			'web_path' => array(),
			'member_var' => array()
		);
		$this->load_stack = array();
	}
	
	public function get_web_paths() {
		return $this->assignments['web_path'];
	}
	
	public function get_plugins($path_prefix) {
		$plugin_dir = trim($this->config['global']['path']['absolute'], "/") . "/" . trim($path_prefix, "/");
		$plugin_dir = trim($plugin_dir, "/") . "/plugins";
		$plugin_dir = "/" . ltrim($plugin_dir, "/");
		
		$plugin_list = array();
		
		if ($handle = opendir($plugin_dir) ) {
			// Wrap the included config file in a function to limit variable scope
			function _read_plugin_config($plugin_dir) {
				ob_start();
				include $plugin_dir . "/config.php";
				ob_end_clean();
				
				if (isset($plugin_config) )
					return $plugin_config;
				return NULL;
			}
			
			$relative_path = str_replace($this->config['global']['path']['absolute'], "", $plugin_dir);
			
			debug_out("Scanning " . $relative_path . "...");
			while ( ($file_name = readdir($handle) ) !== FALSE) {
				if ($file_name == '.' || $file_name == '..') {
					continue;
				}
					
				$plugin_config = _read_plugin_config($plugin_dir . "/" . $file_name);
				if (is_null($plugin_config) ) {
					debug_out("Invalid config for " . $plugin_dir . "/" . $file_name);
				}
				else {
					$plugin_config['location'] = $relative_path . "/" . $file_name;
					$plugin_list[] = $plugin_config;
				}
			}
		}
		else {
			debug_out("Invalid plugin directory.");
		}
		
		return $plugin_list;
	}
	
	public function load_plugin($plugin_config) {
		$short = $plugin_config['short'];
		
		// Make sure the plugin isn't already loaded.
		if (isset($this->loaded[$short]) ) {
			return FALSE;
		}
		
		// Make sure the plugin isn't already on the stack.
		if (isset($this->load_stack[$short]) ) {
			return TRUE;
		}
		
		// Add the plugin to the stack
		$this->load_stack = array_merge(array($short => $plugin_config), $this->load_stack);
		
		// Run the stack processor to attempt to load this plugin and any plugins depending on it.
		return $this->_process_load_plugin_stack();
	}
	
	protected function _process_load_plugin_stack() {
		// Run through each plugin on the stack and check if its dependencies are met (if any)
		foreach ($this->load_stack as $name => $plugin) {
			debug_out("* {$name}: Attempting to load...");
			
			$plugin_location = $this->config['global']['path']['absolute'] . $plugin['location'];
			
			// Assume dependencies are satisfied
			$dependencies_satisfied = TRUE;
			// Check dependencies
			if (isset($plugin['dependencies']) ) {
				foreach ($plugin['dependencies'] as $dependency) {
					if (!isset($this->loaded[$dependency]) ) {
						$dependencies_satisfied = FALSE;
						break;
					}
				}
			}
			
			// All dependencies loaded?
			if ($dependencies_satisfied) {
				debug_out("+ {$name}: Dependencies satisfied...");
				// Assume requests are available, and required files are present
				$requests_available = TRUE;
				$files_present = TRUE;
				
				// Check for request conflicts and missing base files
				if (isset($plugin['requests']) ) {
					foreach ($plugin['requests'] as $type => $request_group) {
						if ($type == "member_var")
							$location = $plugin_location . "/helper/";
						
						foreach ($request_group as $request => $request_target) {
							if (isset($location) && !file_exists($location . $request_target . ".php") ) {
								$files_present = FALSE;
							}
							if (isset($this->assignments[$type][$request]) ) {
								$requests_available = FALSE;
							}
						}
					}
				}
				
				// Are there no conflicts, and are all files actually there?
				if ($requests_available && $files_present) {
					debug_out("+ {$name}: Loading...");
					// Go ahead and officially load the plugin
					$this->loaded[$name] = $plugin;
					unset($this->load_stack[$name]);
					// Assign requested settings
					foreach ($plugin['requests'] as $type => $request_group) {
						if ($type == 'member_var') {
							$path = $plugin['location'] . "/helper/";
						}
							
						foreach ($request_group as $request => $request_target) {
							// Register the assignment with an un-altered "class" name, and the plugin path
							$new_assignment = array(
								'plugin' => $name,
								'class' => $request_target,
								'path' => $plugin['location']
							);
							if (isset($plugin['singleton']) && $plugin['singleton']) {
								$new_assignment['singleton'] = true;
							}
							$this->assignments[$type][$request] = $new_assignment;

							if (isset($path) ) {
								include $path . $request_target . ".php";
							}
						}
					}
					
					// Run the stack processor to load plugins with newly-satisfied dependencies
					return $this->_process_load_plugin_stack();
				}
				else {
					if (!$requests_available) {
						$v = "Conflicts detected";
					}
					else {
						$v = "Missing file";
					}
					debug_out("- {$name}: {$v}. Plugin not loaded...");
				}
			}
			else {
				debug_out("* {$name}: Unsatisfied dependencies. Plugin not loaded...");
			}
		}
		
		return TRUE;
	}
}

?>
