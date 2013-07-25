<?php

class Error_Controller extends FN_Controller {
	public function __construct() {
		parent::__construct();
	}

	public function e404() {
		header("HTTP/1.0 404 Not Found");
		
		$this->data['page' ] = '404';
		$this->data['title'] = 'Not Found';
		
		$this->load_view('error/404'  , true);
	}
	public function e403() {
		header("HTTP/1.0 403 Forbidden");
		
		$this->data['page' ] = '403';
		$this->data['title'] = "Access Forbidden";
		
		$this->load_view('error/403'  , true);
	}
}

?>
