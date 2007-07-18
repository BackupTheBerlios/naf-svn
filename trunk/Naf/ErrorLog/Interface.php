<?php

interface Naf_ErrorLog_Interface
{
	function write(Exception $e);
}