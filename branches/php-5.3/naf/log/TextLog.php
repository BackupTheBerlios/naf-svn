<?php

/**
 * Null-logger
 * 
 * $Id$
 * 
 * @package naf::log
 */

namespace naf::log;

class TextLog extends AbstractLog 
{
	/**
	 * Displays exception information
	 */
	function run()
	{
		echo get_class($this->exception) . ":\n";
		echo $this->exception->getMessage() . "\n";
		echo $this->exception->getTraceAsString();
	}
}