<?php

$routes_config = array(
	"#^/$#"                                       => array('path' => '/', 'controller' => "home", 'method' => "index"),
	"#^/([[:^digit:]]+?)$#"                       => array('path' => '/', 'controller' => 1,      'method' => "index"),
	"#^(/.+)/([[:^digit:]]+?)/([[:^digit:]]+?)$#" => array('path' => 1,   'controller' => 2,      'method' => 3      ),
);

?>