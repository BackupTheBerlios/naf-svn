<?php

/**
 * Object not found exception
 * 
 * $Id$
 * 
 * @package naf::err
 */

namespace naf::err;

class NotFoundError extends Exception {
	function exposeStatus()
	{
		header("HTTP/1.0 404 Not Found", true, 404);
	}
}