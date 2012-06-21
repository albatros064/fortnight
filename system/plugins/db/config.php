<?php

$plugin_config = array(
	'name'      => "Database Helper",
	'short'     => "db",
	'version'   => "0.1",
	'type'      => "helper",
	'singleton' =>  true,
	
	'requests' => array(
		'member_var' => array('Db' => "db")
	)
);

?>
