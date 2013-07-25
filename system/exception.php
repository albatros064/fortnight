<?php

class PageRoutingException extends Exception {
	public function __construct($page_route) {
		parent::__construct();
		$this->page_route = $page_route;
	}

	public function render() {
		header($this->page_route['header']);
		pr($this->page_route['header']);
		pr($this->page_route['message']);
		die();
	}
}

?>
