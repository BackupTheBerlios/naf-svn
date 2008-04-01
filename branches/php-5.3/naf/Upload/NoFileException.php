<?php

class Naf_Upload_NoFileException extends Naf_Upload_Exception {
	function __construct()
	{
		parent::__construct("No file has been uploaded");
	}
}
