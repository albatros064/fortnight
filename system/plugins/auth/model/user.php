<?php

class User_Model extends FN_Model {
	public function __construct() {
		parent::__construct();

		$this->_prefix = "fn_";
	}
}

?>
