<?php

/**
 * make it easier to run in command-line mode
 */

class Cli {
	private $_defaults = array('verbose' => false);
	private $_args = array();
	function __construct($defaults, $setters = array())
	{
		$this->_defaults = array_merge($this->_defaults, $defaults);
		foreach ($_SERVER['argv'] as $shellArg)
		{
			@list($arg, $value) = explode("=", $shellArg, 2);
			$args[$arg] = array_key_exists($arg, $setters) ? $setters[$arg]($value) : $value;
		}
		$this->_args = array_merge($this->_defaults, $args);
	}
	function println($s)
	{
		if ($this->verbose) print $s . "\n";
	}
	function __get($name)
	{
		return @$this->_args[$name];
	}
	function __set($name, $value)
	{
		$this->_args[$name] = $value;
	}
	static function date($arg)
	{
		return date("Y-m-d", strtotime($arg));
	}
	static function commaSplit($arg)
	{
		return preg_split("/,\s*/", $arg);
	}
}