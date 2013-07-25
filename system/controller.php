<?php

abstract class FN_Controller extends FN_Base {

	public $request = null;
	public $access_restrictions = null;

	public function __construct() {
		parent::__construct();
		
		$this->data = array(
			'page'  => 'generic'
		);
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
			return $this;
		}

		return $view_contents;
	}

	protected function page_url($path = '/') {
		$prefix = trim($this->request['route']['prefix'], "/");
		$base = trim($this->config['global']['path']['base'], "/");
		if ($prefix) {
			$prefix = "/{$prefix}";
		}
		if ($base) {
			$base = "/{$base}";
		}
		return "{$base}{$prefix}/" . trim($path, "/");
	}

	protected function image_url($image) {
		return $this->_base_asset_url() . "image/" . ltrim($image, "./");
	}
	protected function js_url($js) {
		return $this->_base_asset_url() . "js/" . ltrim($js, "./");
	}
	protected function css_url($css) {
		return $this->_base_asset_url() . "css/" . ltrim($css, "./");
	}

	protected function _base_asset_url() {
		$prefix = trim($this->request['route']['file_prefix'], "/");
		$base = trim($this->config['global']['path']['base'], "/");
		if ($prefix) {
			$prefix = "/{$prefix}";
		}
		if ($base) {
			$base = "/{$base}";
		}
		return "{$base}{$prefix}/asset/";
	}
	
	protected function restrict_access($auth_level = 1, $pages = NULL, $exclude = FALSE) {
		if ($pages === NULL && !$exclude) {
			$this->access_restrictions = $auth_level;
		}
		else if ($pages === NULL) {
		}
	}
	
	protected function redirect($url) {
		header("Location: {$this->page_url($url)}");
		die();
	}
}

?>
