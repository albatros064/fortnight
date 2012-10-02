<?php

define('OUTPUT_DEBUG_ERRORS', TRUE);
define('OUTPUT_DEBUG_MESSAGES', TRUE);

$global_config = array(
	'path' => array(
		'absolute' => "/home/npowell/public_html/fortnight/",
		'base' => "~npowell/fortnight",	
		'system' => "system",
		'system_plugins' => "system/plugins"
	),
	
	'vars' => array(
		'allow_uri_vars' => TRUE,
		'allow_query_string_override' => TRUE
	),

	'db' => array(
		'host' => 'localhost',
		'user' => 'fortnight',
		'password' => 'a2LFtnQ8mrCAYteP',
		'database' => 'fortnight'
	)
);

?>
