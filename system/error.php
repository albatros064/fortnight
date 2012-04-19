<?php


// Default error output to FALSE. Override before this file is included.
if (!defined('OUTPUT_DEBUG_ERRORS') )
	define('OUTPUT_DEBUG_ERRORS', FALSE);

error_reporting(E_ALL | E_STRICT);


// Pretty print
function pr($i)
{
	if (OUTPUT_DEBUG_ERRORS)
	{
		echo "<pre>";
		print_r($i);
		echo "</pre>";
	}
}


function get_trace_string($trace, $color)
{
	$output = '' .
		'<tr style="font-size:15px;background:'.$color.'">' .
			'<td colspan="5">Stack Trace:</td>' .
		'</tr>' .
		'<tr style="background-color:'.$color.'">' .
			'<th>#</th><th colspan="2">Function</th><th>File</th><th>Line</th>' .
		'</tr>';
	
	$i = 0;
	foreach ($trace as $call)
	{
		$i++;
		$function = '';
		if (isset($call['class']) )
			$function .= $call['class'];
		if (isset($call['type']) )
			$function .= $call['type'];
		$function .= $call['function']."(";
		if (isset($call['args']) )
		{
			foreach ($call['args'] as $arg)
			{
				if (is_string($arg) )
					$arg = '"'.str_replace("\"", "\\\"", $arg) . '"';
				$function .= $arg . ', ';
			}
		}
		$function = rtrim($function, ", ").")";
		
		$file = "/".str_replace($_SERVER['DOCUMENT_ROOT'], "", str_replace("\\", "/", $call['file']) );
		
		$output .= '' .
			'<tr>' .
				'<td>'.$i.'</td><td colspan="2">'.$function.'</td><td>'.$file.'</td><td>'.$call['line'].'</td>' .
			'</tr>';
	}
	
	return $output;
}

function fortnight_generic_handler($errno, $errstr, $errfile, $errline, $trace)
{
	$error_types = Array(
		E_ERROR => 'Fatal Error',
		E_WARNING => 'Warning',
		E_PARSE => 'Parse Error',
		E_NOTICE => 'Notice',
		E_CORE_ERROR => 'Fatal Core Error',
		E_CORE_WARNING => 'Core Warning',
		E_COMPILE_ERROR => 'Compilation Error',
		E_COMPILE_WARNING => 'Compilation Warning',
		E_USER_ERROR => 'Triggered Error',
		E_USER_WARNING => 'Triggered Warning',
		E_USER_NOTICE => 'Triggered Notice',
		E_STRICT => 'Deprecation Notice',
		E_RECOVERABLE_ERROR => 'Catchable Fatal Error'
	);
	
	if ($trace === FALSE)
	{
		$color = "#a48f77";
		$color_heading = "#ffaa33";
	} else {
		$color = "#aa9999";
		$color_heading = "#aa3333";
	}
	
	if (isset($error_types[$errno]) )
		$heading = $error_types[$errno];
	else
		$heading = "Uncaught Exception";
	
	$errfile = "/".str_replace($_SERVER['DOCUMENT_ROOT'], "", str_replace("\\", "/", $errfile) );
	
	$output = '' .
		'<table cellspacing="0" border="1" cellpadding="2" style="font-family:\'Courier New\',monospace;font-size:12px;background:#999;width:960px">' .
			'<tr>' .
				'<th style="font-size:140%;background:'.$color_heading.'" colspan="5">'.$heading.':</th>' .
			'</tr>' .
			'<tr>' .
				'<td style="background-color:'.$color.';max-width:100px" colspan="2">File</td><td colspan="3">'.$errfile.'</td>' .
			'</tr>' .
				'<td style="background-color:'.$color.'" colspan="2">Line</td><td colspan="3">'.$errline.'</td>' .
			'</tr>' .
				'<td style="background-color:'.$color.'" colspan="2">Code</td><td colspan="3">'.$errno.'</td>' .
			'</tr>' .
			'<tr>' .
				'<td style="background-color:'.$color.'" colspan="2">Message</td><td colspan="3">'.$errstr.'</td>' .
			'</tr>' .
			( ($trace !== FALSE && is_array($trace) && !empty($trace) ) ? get_trace_string($trace, $color) : '') .
		'</table>';
	
	if (OUTPUT_DEBUG_ERRORS)
		return $output;
	return '';
}

function fortnight_error_handler($errno, $errstr, $errfile, $errline)
{
	echo fortnight_generic_handler($errno, $errstr, $errfile, $errline, false);
	return true;
}

function fortnight_exception_handler($e)
{
	echo fortnight_generic_handler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace() );
}

function fortnight_fatal_handler($out = '')
{
	$error = error_get_last();
	// Not all of these can be caught (E_PARSE, I'm lookin' at you), but we'll specify them anyway.
	$catch = array(E_ERROR, E_CORE_ERROR, E_CORE_WARNING, E_PARSE, E_COMPILE_ERROR, E_COMPILE_WARNING);
	$warn = array(E_CORE_WARNING, E_COMPILE_WARNING);
	if ($error !== NULL && in_array($error['type'], $catch) )
	{
		if (in_array($error['type'], $warn) )
			$tr = FALSE;
		else
			$tr = TRUE;
		$out .= fortnight_generic_handler($error['type'], $error['message'], $error['file'], $error['line'], $tr);
	}
	return $out;
}

// Set error handlers
set_error_handler    ("fortnight_error_handler"    ); # Recoverable errors.
set_exception_handler("fortnight_exception_handler"); # Fatal uncaught exceptions
ob_start             ("fortnight_fatal_handler"    ); # All other catchable errors that wouldn't otherwise be caught

?>