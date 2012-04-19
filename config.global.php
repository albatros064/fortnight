<?php

define('OUTPUT_DEBUG_ERRORS', TRUE);

$global_config = array(
	'path' => array(
		'base' => "/fortnight",	
		'system' => "/system",
		'applications' => "/applications",
		'system_plugins' => "/system/plugins",
	),
	
	'vars' => array(
		'allow_uri_vars' => TRUE,
		'allow_query_string_override' => TRUE,
	),
);

?>