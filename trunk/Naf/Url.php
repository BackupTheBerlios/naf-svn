<?php

/**
 * Compose Naf-style URLs.
 * Take care of SESSID when needed.
 */

class Naf_Url {
	protected $_base = './';
	protected $_appendSid;
	
	function __construct($base = null)
	{
		if (null !== $base)
			$this->_base = $base;
		
		$sessionName = session_name();
		if ((! isset($_COOKIE[$sessionName])) && 
			(isset($_GET[$sessionName]) || isset($_POST[$sessionName])) && 
			ini_get('session.use_trans_sid'))
		{
			$this->_appendSid = true;
		}
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
		if ($this->_appendSid && $sid = session_id())
			$params[session_name()] = $sid;
		
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