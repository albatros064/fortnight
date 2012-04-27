<?php

$plugin_config = array(
	'name'    => "Site Administration",
	'version' => "0.1",
	'short'   => "admin",
	'type'    => "content",
	
	'dependencies' => array('db','auth','validate'),
	'requests' => array(
		'web_path' => "/admin",
	),
);