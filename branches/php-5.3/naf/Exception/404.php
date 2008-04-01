<?php

class Naf_Exception_404 extends Exception {
	function __construct($title = '404 Not Found', $view = '404')
	{
		parent::__construct($title);
	}
}