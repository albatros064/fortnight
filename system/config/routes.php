<?php

$routes_config = array(
	"#^/$#"                                           => array('path' => '/', 'controller' => "home", 'method' => "index"),
	"#^/([^0-9^\/][^\/]*?)$#"                         => array('path' => '/', 'controller' => 1,      'method' => "index"),
	"#^/([^0-9^\/][^\/]*?)/([^0-9^\/][^\/]*?)$#"      => array('path' => '/', 'controller' => 1,      'method' => 2      ),
	"#^(/.+)/([^0-9^\/][^\/]*?)/([^0-9^\/][^\/]*?)$#" => array('path' => 1,   'controller' => 2,      'method' => 3      ),
);

?>
