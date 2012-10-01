<?php

class Home_Controller extends FN_Controller {
	public function __construct() {
		parent::__construct();
	}

	public function party() {
		$revision = $this->Template->revision();
		$revision = $revision[0];
		pr($revision->created() );
		pr($this->Template->user()->password) );
	}
}

?>
