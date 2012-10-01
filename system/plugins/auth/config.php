<?php

$plugin_config = array(
	'name'  => "User Authorization",
	'short' => "auth",
	'version' => "0.1",
	'type'  => "helper",
	
	'dependencies' => array('db'),
	'requests' => array(
		'helper_var' => array('Auth' => "auth"),
		'model_var' => array('User' => "user")
	),
);

?>
