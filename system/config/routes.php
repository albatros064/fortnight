<?php

$routes_config = array(
	"#^/$#"                      => array('path' => '', 'controller' => "home", 'method' => "index", 'arg' => NULL),
	"#^/([^/]+?)$#"              => array('path' => '', 'controller' => 1,      'method' => "index", 'arg' => NULL),
	"#^/(.+)/([[:^digit:]]+?)$#" => array('path' => '', 'controller' => 1,      'method' => 2,       'arg' => NULL),
	"#^/(.+)/(.+?)/(.+?)$#"      => array('path' => '', 'controller' => 1,      'method' => 2,       'arg' => 3   ),
);

?>