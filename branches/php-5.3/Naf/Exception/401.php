<?php

class Naf_Exception_401 extends Exception {
	function __construct($title = '401 Unauthorized', $view = '401')
	{
		parent::__construct($title);
		Naf::$response->setView($view);
		Naf::$response->setTitle($title);
		Naf::$response->setStatus('401 Unauthorized');
	}
}