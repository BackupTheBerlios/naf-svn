<?php

class Naf_Url {
	protected $_base = './';
	
	function __construct($base = null)
	{
		if (null !== $base)
			$this->_base = $base;
	}
	
	/**
	 * @param string $action If not specified, the current action is used
	 * @param array $params
	 * @return string
	 */
	function compose($action = null, array $params = array(), $separator = '&')
	{
		if (null === $action)
			$action = Naf::currentAction();
		
		$params['ctrl'] = str_replace('/', '.', $action);
		return $this->_base . '?' . http_build_query($params, null, $separator);
	}
	
	/**
	 * @param string $action If not specified, the current action is used
	 * @param array $params
	 * @return string
	 */
	function composeXml($action = null, array $params = array())
	{
		return $this->compose($action, $params, '&amp;');
	}
}