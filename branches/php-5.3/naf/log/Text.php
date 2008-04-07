<?php

/**
 * Null-logger
 * 
 * $Id$
 * 
 * @package naf::log
 */

namespace naf::log;

class TextLog
{
	/**
	 * Displays exception information
	 */
	function write(Exception $e)
	{
		echo get_class($e) . ":\n";
		echo $e->getMessage() . "\n";
		echo $e->getTraceAsString();
	}
}