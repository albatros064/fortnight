<?php

class Home_Controller extends FN_Controller {
	public function __construct() {
		parent::__construct();
	}

	public function _t_party() {
		$revision = $this->Template->revision();
		$revision = $revision[0];
		pr($revision->created() );
		pr($this->Template->user()->password() );
	}
}

?>
