<?php

$plugin_config = array(
	'name'  => "User Authorization",
	'short' => "auth",
	'version' => "0.1",
	'type'  => "helper",
	
	'dependencies' => array('db'),
	'requests' => array(
		'member_variable' => 'Auth'
	),
);

?>