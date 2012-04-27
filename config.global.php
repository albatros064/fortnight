<?php

define('OUTPUT_DEBUG_ERRORS', TRUE);
define('OUTPUT_DEBUG_MESSAGES', TRUE);

$global_config = array(
	'path' => array(
		'absolute' => "/wamp/www/fortnight",
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