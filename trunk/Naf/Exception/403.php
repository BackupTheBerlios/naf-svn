<?php

class Naf_Exception_403 extends Exception {
	function __construct($title = '', $view = '403')
	{
		parent::__construct('403 Forbidden: ' . $title);
		Naf::$response->setView($view);
		Naf::$response->setTitle($title);
		Naf::$response->setStatus('403 Forbidden');
	}
}