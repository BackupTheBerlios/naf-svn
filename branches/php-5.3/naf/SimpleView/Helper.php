<?php

class Naf_SimpleView_Helper {
	private $view;
	final function __construct(Naf_SimpleView $view)
	{
		$this->view = $view;
	}
	final function __get($name)
	{
		return $this->view->__get($name);
	}
}