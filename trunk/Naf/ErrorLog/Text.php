<?php

class Naf_ErrorLog_Text implements Naf_ErrorLog_Interface
{
	/**
	 * Displays exception information
	 */
	function write(Exception $e)
	{
		echo get_class($e) . ":\n";
		echo $e->getMessage() . "\n";
		echo $e->getTraceAsString();
	}
}