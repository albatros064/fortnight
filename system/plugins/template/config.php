<?php

$plugin_config = array(
	'name'      => "Template Model",
	'short'     => "template",
	'version'   => "0.1",
	'type'      => "model",
	'singleton' =>  true,

	'dependencies' => array('db'),
	'requests' => array(
		'model_var' => array('Template' => "template", "Revision" => "revision")
	)
);

?>
