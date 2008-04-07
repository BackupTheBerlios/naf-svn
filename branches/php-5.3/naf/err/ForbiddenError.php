<?php

/**
 * No permissions exception
 * 
 * $Id$
 * 
 * @package naf::err
 */

namespace naf::err;

class ForbiddenError extends Exception {
	function exposeStatus()
	{
		header("HTTP/1.0 403 Forbidden", true, 403);
	}
}