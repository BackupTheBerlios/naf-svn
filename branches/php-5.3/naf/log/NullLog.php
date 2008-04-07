<?php

/**
 * Null-logger
 * 
 * $Id$
 * 
 * @package naf::log
 */

namespace naf::log;

class NullLog
{
	function write(Exception $e)
	{
		// do nothing
	}
}