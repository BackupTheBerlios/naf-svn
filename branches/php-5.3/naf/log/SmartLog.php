<?php

/**
 * Smart-logger. initializes either TextLog or Browser depending on SAPI
 * 
 * $Id$
 * 
 * @package naf::log
 */

namespace naf::log;

class SmartLog extends AbstractLog
{
	/**
	 * Displays exception information
	 */
	function run()
	{
		if (PHP_SAPI == 'cli')
		{
			$logger = new TextLog($this->exception);
		} else {
			$logger = new BrowserLog($this->exception);
		}
		
		return $logger->run();
	}
}