<?php

abstract class FN_Controller extends FN_Base {

	public $request = null;

	public function __construct() {
		parent::__construct();
	}

	protected function load_view($view_name, $echo = FALSE) {
		$view_file = ltrim($view_name, "./") . ".php";
		$view_path = rtrim($this->config['global']['path']['absolute'], "/") . "/" . trim($this->request['route']['file_prefix'], "/") . "/view/";
		$view_file = $view_path . $view_file;

		if (file_exists($view_file) ) {
			ob_start();
			include $view_file;
			$view_contents = ob_get_clean();
		}
		else {
			throw new Exception("View '{$view_name}' not found.");
		}

		if ($echo) {
			echo $view_contents;
		}

		return $view_contents;
	}

	protected function page_url($path) {
		return "/" . trim($this->config['global']['path']['base'], "/") . "/" . trim($this->request['route']['prefix'], "/") . "/" . trim($path, "/");
	}

	protected function image_url($image) {
		return $this->_base_asset_url() . "/view/images/" . ltrim($image, "./");
	}
	protected function js_url($js) {
		return $this->_base_asset_url() . "/view/js/" . ltrim($js, "./");
	}
	protected function css_url($css) {
		return $this->_base_asset_url() . "/view/css/" . ltrim($css, "./");
	}

	protected function _base_asset_url() {
		return "/" . trim($this->config['global']['path']['base'], "/") . trim($this->request['route']['file_prefix'], "/");
	}
}

?>
