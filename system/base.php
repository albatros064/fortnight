<?php

abstract class FN_Base {

	public    static $_global_config  = null;
	protected static $_plugin_manager = null;
	protected static $_file_prefix    = null;

	function __construct() {
		if (is_null(self::$_global_config) ) {
			throw new Exception("Global config not set.");
		}

		$this->config = array(
			'global' => self::$_global_config
		);
	}

	protected function _load_config_file($config, $ignore_not_found = FALSE) {
		$_config_file_name = "{$this->config['global']['path']['system']}/config/{$config}.php";
		$_config_var_name  = "{$config}_config";

		if (file_exists($_config_file_name) ) {
			ob_start();
			include $_config_file_name;
			ob_clean();
		}

		if (isset($$_config_var_name) ) {
			return $$_config_var_name;
		}
		
		// If it's not found, either return NULL or throw an exception.
		if ($ignore_not_found) {
			return NULL;
		}
		throw new Exception("Config file not found for {$config}. Aborting.");
	}
	
	public function load_model($model_name, $preload = NULL) {
		$singleton = false;

		$var_name = ucfirst($model_name);
		$class_name = $var_name . "_Model";

		// Check for a global model
		if (isset(self::$_plugin_manager->assignments['model_var'][$model_name]) ) {
			$model_data = self::$_plugin_manager->assignments['model_var'][$model_name];

			$class_name = ucfirst($model_data['class']) . "_Model";

			$singleton = (isset($model_data['singleton']) && $model_data['singleton']);
		}
		else {
			if (!class_exists($class_name) ) {
				// Check for a local model
				$path = rtrim(self::$_global_config['path']['absolute'], "/") . "/" . trim(self::$_file_prefix, "/") . "/model/" . $class_name . ".php";
				if (file_exists($path) ) {
					ob_start();
					include $path;
					ob_end_clean();
				}
			}
		}

		if (class_exists($class_name) ) {
			if ($singleton) {
				$new_class = $class_name::Instance($preload);
			}
			else {
				$new_class = new $class_name;
				if ($preload !== NULL && $preload > 0) {
					$new_class->load($preload);
				}
			}

			$this->$var_name = $new_class;

			return $new_class;
		}

		throw new Exception("Could not find model.");

		return NULL;
	}

	public function load_helper($helper_name) {
		if (isset(self::$_plugin_manager->assignments['helper_var'][$helper_name]) ) {
			$helper_data = self::$_plugin_manager->assignments['helper_var'][$helper_name];

			$class_name = ucfirst($helper_data['class']) . "_Helper";
			$var_name = ucfirst($helper_name);

			$new_class = NULL;

			if (class_exists($class_name) ) {
				if (isset($helper_data['singleton']) && $helper_data['singleton']) {
					$new_class = $class_name::Instance();
				}
				else {
					$new_class = new $class_name;
				}
				$this->$var_name = $new_class;
			}

			return $new_class;
		}
		return NULL;
	}
	

	public function _set_plugin_manager($plugin_manager) {
		if (is_null(self::$_plugin_manager) ) {
			self::$_plugin_manager = $plugin_manager;
		}
	}
}

?>
