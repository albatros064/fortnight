<?php

class Home_Controller extends FN_Controller {
	public function __construct() {
		parent::__construct();
	}

	public function index() {
		pr($this->template);
	}
}

?>
