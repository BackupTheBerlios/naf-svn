<?php

/**
 * Abstract logger
 * 
 * $Id$
 * 
 * @package naf::log
 */

namespace naf::log;

abstract class AbstractLog {
	protected $exception;
	final function __construct(Exception $e)
	{
		$this->exception = $e;
	}
	abstract function run();
}