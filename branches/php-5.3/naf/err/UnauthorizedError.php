<?php

/**
 * Authorization-required exception
 * 
 * $Id$
 * 
 * @package naf::err
 */

namespace naf::err;

class UnauthorizedError extends Exception {
	function exposeStatus()
	{
		header("HTTP/1.0 401 Unauthorized", true, 401);
	}
}