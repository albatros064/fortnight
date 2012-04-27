<?php

class FN_Plugin extends FN_Base
{
}

class FN_Plugin_Manager extends FN_Base
{
	function __construct($global_config)
	{
		parent::__construct();
		$this->config = array(
			'global' => $global_config
		);
		$this->loaded = array();
		$this->load_queue = array();
	}
	
	public function get_plugins($path_prefix)
	{
		$plugin_dir = trim($this->config['global']['path']['absolute'], "/") . "/" . trim($path_prefix, "/");
		$plugin_dir = trim($plugin_dir, "/") . "/plugins";
		$plugin_dir = "/" . ltrim($plugin_dir, "/");
		
		$plugin_list = array();
		
		if ($handle = opendir($plugin_dir) )
		{
			// Wrap the included config file in a function to limit variable scope
			function _read_plugin_config($plugin_dir)
			{
				ob_start();
				include $plugin_dir . "/config.php";
				ob_end_clean();
				
				if (isset($plugin_config) )
					return $plugin_config;
				return NULL;
			}
			
			$relative_path = str_replace($this->config['global']['path']['absolute'], "", $plugin_dir);
			
			debug_out("Scanning " . $relative_path . "...");
			while ( ($file_name = readdir($handle) ) !== FALSE)
			{
				if ($file_name == '.' || $file_name == '..')
					continue;
					
				$plugin_config = _read_plugin_config($plugin_dir . "/" . $file_name);
				if ($plugin_config === NULL)
				{
					debug_out("Invalid config for " . $plugin_dir . "/" . $file_name);
				} else {
					$plugin_config['location'] = $relative_path . "/" . $file_name;
					$plugin_list[] = $plugin_config;
				}
			}
		} else {
			debug_out("Invalid plugin directory.");
		}
		
		return $plugin_list;
	}
	
	public function load_plugin($plugin_config)
	{
		// Add the plugin to the queue
		
		foreach ($
		
		return TRUE;
	}
	
	protected function _process_load_plugin_queue()
	{
		// Check dependencies
		if (isset($plugin_config['dependencies']) )
		{
			foreach ($plugin_config['dependencies'] as $dependency)
			{
				
			}
		}
		
		if ($plugin_config['type'] == 'helper')
		{
			$class_name = ucwords($plugin_config['short']
			$complete_file_path = $this->config['global']['path']['absolute'] .
		}
	}
}

?>