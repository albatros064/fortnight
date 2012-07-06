<?php

class Home_Controller extends FN_Controller {
	public function __construct() {
		parent::__construct();
	}

	public function index() {
		$this->load_view("common/head");
		pr($this->request);
		$this->load_view("common/foot");
	}
}

?>
