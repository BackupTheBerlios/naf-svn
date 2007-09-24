<?php

class Naf_SimpleView {
	
	/**
	 * @var Naf_Response
	 */
	protected $_response;
	
	/**
	 * Assigned variables.
	 *
	 * @var array
	 */
	protected $_vars = array();
	
	/**
	 * @var array
	 */
	protected $_helperList = array();
	
	/**
	 * Where to look for templates
	 *
	 * @var string
	 */
	protected $_scriptPath;
	
	function __construct(Naf_Response $response)
	{
		$this->_response = $response;
		$this->_vars = ($this->_response->export());
	}
	
	function setScriptPath($path)
	{
		$this->_scriptPath = (array) $path;
	}
	
	/**
	 * Render output
	 *
	 * @param string $name View name
	 */
	function render($name)
	{
		$er = error_reporting();
		error_reporting($er & ~E_NOTICE);
		
		foreach ($this->_scriptPath as $dir)
		{
			if (is_file($viewFilename = $dir . $name . '.tpl.php'))
			{
				include $viewFilename;
				return ;
			}
		}
		
		error_reporting($er);
		
		throw new Naf_Exception_404();
	}
	
	function registerHelper($helper)
	{
		if (is_object($helper))
		{
			$this->_helperList[get_class($helper)] = $helper;
		}
		elseif (is_array($helper))
		{
			foreach ($helper as $helperSpec)
				$this->registerHelper($helperSpec);
		}
		else
		{
			$this->_helperList[$helper] = null;
		}
	}
	
	/**
	 * Draw options list for a <select> element
	 *
	 * @param array $options
	 * @param mixed $selected
	 */
	function optionList($options, $selected = null)
	{
		$selected = (array) $selected;
		foreach ($options as $value => $text)
		{
			echo '<option value="' . htmlspecialchars($value, ENT_QUOTES) . 
					'" ' . (in_array($value, $selected) ? 'selected="true"' : '') . '>' . 
					htmlspecialchars($text, ENT_QUOTES) . '</option>' . "\n";
		}
	}
	
	/**
	 * Escape value to be displayed in HTML page
	 *
	 * @param string $value
	 * @param int $quoteStyle
	 * @param string $charset
	 * @return string
	 */
	function escape($value, $quoteStyle = ENT_QUOTES, $charset = null) {
		if (null === $charset) $charset = Naf::$response->getCharset();
		return htmlspecialchars($value, $quoteStyle, $charset);
	}
	
	function __call($method, $args)
	{
		foreach ($this->_helperList as $className => $helper)
		{
			if (! is_object($helper))
				$this->_helperList[$className] = $helper = new $className;
			
			if (method_exists($helper, $method))
				return call_user_func_array(array($helper, $method), $args);
		}
		
		throw new Exception('Method ' . $method . ' is not present in View helpers');
	}
	
	function get($name, $default = null)
	{
		return array_key_exists($name, $this->_vars) ? $this->_vars[$name] : $default;
	}
	
	function __get($name)
	{
		if (array_key_exists($name, $this->_vars))
			return $this->_vars[$name];
		
		throw new Naf_Exception('Variable ' . $name . ' could not be found');
	}
	
	function __set($name, $value)
	{
		$this->_vars[$name] = $value;
	}
}