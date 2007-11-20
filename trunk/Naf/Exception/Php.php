<?php

class Naf_Exception_Php extends Exception {
	function __construct($errstr, $errno)
	{
		parent::__construct($errstr, $errno);
	}
}