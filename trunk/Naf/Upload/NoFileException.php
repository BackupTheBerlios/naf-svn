<?php

class Naf_Upload_NoFileException extends Exception {
	function __construct()
	{
		parent::__construct("No file has been uploaded");
	}
}
