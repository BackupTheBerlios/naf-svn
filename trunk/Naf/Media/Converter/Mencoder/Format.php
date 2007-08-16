<?php

abstract class Naf_Media_Converter_Mencoder_Format {
	
	/**
	 * @var Naf_Media_Info
	 */
	protected $info;
	
	/**
	 * Constructor
	 *
	 * @param Naf_Media_Info $i
	 */
	final function __construct(Naf_Media_Info $i)
	{
		$this->info = $i;
	}
	/**
	 * Configure shell-command
	 *
	 * @param Naf_ShellCmd $c
	 */
	abstract function configure(Naf_ShellCmd $c);
	
	/**
	 * Get filename for the format.
	 * Usually can be left unchanged
	 *
	 * @param string $filename
	 * @return string
	 */
	function filename($filename)
	{
		return $filename;
	}
	
	protected function extension($filename)
	{
		return substr($filename, strrpos($filename, '.') + 1);
	}
}