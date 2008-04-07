<?php

/**
 * $Id$
 * 
 * @package naf.util
 * @subpackage Upload
 * @copyright Victor Bolshov <crocodile2u@gmail.com>
 */

namespace naf::util::Upload;

class NoFileFault extends Fault {
	function __construct()
	{
		parent::__construct("No file has been uploaded");
	}
}
