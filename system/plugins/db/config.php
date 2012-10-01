<?php

$plugin_config = array(
	'name'      => "Database Helper",
	'short'     => "db",
	'version'   => "0.1",
	'type'      => "helper",
	'singleton' =>  true,
	
	'requests' => array(
		'helper_var' => array('Db' => "db")
	)
);

?>
