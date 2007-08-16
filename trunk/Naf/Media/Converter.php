<?php

abstract class Naf_Media_Converter 
	extends Naf_Media_ShellCmdWrapper
{
	/**
	 * @var Naf_Media_Info
	 */
	protected $outputInfo;
	
	/**
	 * @var string
	 */
	protected $source;
	
	/**
	 * Constructor
	 *
	 * @param string $source
	 * @throws Naf_Media_Exception
	 */
	function __construct($command, $source)
	{
		parent::__construct($command);
		
		if (! is_file($source) || ! is_readable($source))
			throw new Naf_Media_Exception("File is unreadable or does not exist");
		
		$this->source = $source;
		$this->outputInfo = new Naf_Media_Info();
	}
	
	/**
	 * __magic__ call: wrap non-existant methods.
	 * Delegates to $this->outputInfo.
	 *
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	final function __call($name, $args)
	{
		if (method_exists($this->outputInfo, $name))
			return call_user_func_array(array($this->outputInfo, $name), $args);
		
		throw new Naf_Media_Exception("Method $name does not exist");
	}
	
	/**
	 * Convert media according to specifications in $this->outputInfo.
	 *
	 * @param string $filename - passed by reference because sometimes there is a need to change the filename,
	 * 								or otherwise the backend will produce an error
	 * @return Naf_Media_Info - information for the newly created
	 * @throws Naf_Media_Exception
	 */
	abstract function convert(&$filename, $start = 0, $duration = null);
}