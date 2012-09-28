<?php

class FN_Template extends FN_Base {
	protected $_data;

	public function __construct() {
		parent::__construct();
		$this->_data = NULL;
	}

	public function load($request) {
		if (!$this->loaded() ) {
			$this->load_helper("Db");

			$num_rows = $this->Db->query("SELECT * FROM fn_page WHERE page_slug = '{$request}'");
			if ($num_rows == 1) {
				$this->_data = $this->Db->get_row();
				$template = "/" . trim($this->_data['page_template'], "/");
				return array('request' => $template);
			}
		}
		
		return FALSE;
	}

	public function loaded() {
		return $this->_data !== NULL;
	}
}

?>
