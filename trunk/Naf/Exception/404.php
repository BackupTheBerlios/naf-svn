<?php

class Naf_Exception_404 extends Exception {
	function __construct($title = '404 Not Found', $view = '404')
	{
		parent::__construct($title);
		Naf::$response->setView($view);
		Naf::$response->setTitle($title);
		Naf::$response->setStatus('404 Not Found');
	}
}