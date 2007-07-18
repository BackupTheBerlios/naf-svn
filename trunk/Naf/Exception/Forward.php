<?php

/**
 * This exception is thrown once it is required to switch to new Action
 */

class Naf_Exception_Forward extends Exception {
	
	/**
	 * @var string
	 */
	private $_action;
	
	/**
	 * @var string
	 */
	private $_replaceAction;
	
	/**
	 * Constructor
	 *
	 * @param string $action
	 */
	function __construct($action, $replaceAction)
	{
		$this->_action = $action;
		$this->_replaceAction = $replaceAction;
	}
	
	/**
	 * Where are we forwarded ?
	 *
	 * @return string Forwarded action
	 */
	function where()
	{
		return $this->_action;
	}
	
	/**
	 * Must we replace the current action ?
	 *
	 * @return bool
	 */
	function replace()
	{
		return $this->_replaceAction;
	}
}