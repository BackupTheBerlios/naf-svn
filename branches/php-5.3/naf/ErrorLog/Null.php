<?php

class Naf_ErrorLog_Null implements Naf_ErrorLog_Interface
{
	function write(Exception $e)
	{
		// do nothing
	}
}