<?php

class Error_Controller extends FN_Controller {
	public function __construct() {
		parent::__construct();
	}

	public function e404() {
		$this->load_view('Error/404', true);
	}
}

?>
