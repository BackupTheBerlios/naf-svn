<?php

/**
 * Compose Naf-style URLs.
 * Take care of SESSID when needed.
 */

class Naf_Url {
	protected $_base = './';
	protected $_appendSid;
	protected $_persistent = array();
	
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
		$sessionName = session_name();
		if ((! isset($_COOKIE[$sessionName])) && 
			(isset($_GET[$sessionName]) || isset($_POST[$sessionName])) && 
			ini_get('session.use_trans_sid'))
		{
			$params[session_name()] = $sid;
		}
		
		return $this->_base . '?' . http_build_query(array_merge($this->_persistent, $params), null, $separator);
	}
	
	function setBase($base)
	{
		$this->_base = $base;
	}
	
	function setPersistent($vars)
	{
		foreach ($vars as $name => $default)
		{
			if (array_key_exists($name, $_GET))
				$this->_persistent[$name] = $_GET[$name];
			else
				$this->_persistent[$name] = $default;
		}
	}
	
	/**
	 * Get persistent URL vars
	 *
	 * @param string $action action to override Naf::currentAction()
	 * @param bool $asHidden when set to TRUE, return value is a string containing HTML hiddent inputs list
	 * @return array | string
	 */
	function getPersistent($action = null, $asHidden = false)
	{
		if ($asHidden)
		{
			$tmp = $this->_persistent;
			$tmp['ctrl'] = $action ? $action : Naf::currentAction();
			$html = "";
			foreach ($tmp as $name => $value)
				if (is_scalar($value))
					$html .= '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES) . '" value="' . htmlspecialchars($value, ENT_QUOTES) . '" />';
			
			return $html;
		}
		
		return $this->_persistent;
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