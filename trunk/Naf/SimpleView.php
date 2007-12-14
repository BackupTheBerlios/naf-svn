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
	
	/**
	 * Named buffers for wrap
	 *
	 * @var array
	 */
	protected $_buffers = array();
	
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
	 * @param string $name Template name
	 * @param array $localVars associative array of variables in local scope for this template
	 */
	function render($name, $localVars = null)
	{
		$er = error_reporting();
		error_reporting($er & ~E_NOTICE);
		
		foreach ($this->_scriptPath as $dir)
		{
			if (is_file($viewFilename = $dir . $name . '.tpl.php'))
			{
				if (is_array($localVars))
				{
					extract($localVars);
				}
				
				$this->_buffers[] = array();
				end($this->_buffers);
				
				include $viewFilename;
				
				$key = key($this->_buffers);
				foreach ($this->_buffers[$key] as $template => $placeholders)
				{
					foreach ($placeholders as $name => $tokens)
					{
						for ($i = 0; $i < count($tokens); ++$i)
						{
							ob_end_flush();
						}
					}
				}
				foreach ($this->_buffers[$key] as $template => $placeholders)
				{
					$vars = array();
					foreach ($placeholders as $name => $tokens)
					{
						$vars[$name] = implode("", $tokens);
					}
					$this->render($template, $vars);
				}
				
				array_pop($this->_buffers);
				end($this->_buffers);
				
				return ;
			}
		}
		
		error_reporting($er);
		
		throw new Naf_Exception_404();
	}
	
	function fetch($name, $localVars = null)
	{
		ob_start();
		try {
			$this->render($name, $localVars);
		} catch (Exception $e) {
			ob_end_clean();
			throw $e;
		}
		return ob_get_clean();
	}
	/**
	 * Wrap the buffer into another template
	 *
	 * @param string $template
	 * @param string $placeholder
	 */
	function wrap($template, $placeholder)
	{
		$key = key($this->_buffers);
		if (! array_key_exists($template, $this->_buffers[$key]))
		{
			$this->_buffers[$key][$template] = array();
		}
		if (! array_key_exists($placeholder, $this->_buffers[$key][$template]))
		{
			$this->_buffers[$key][$template][$placeholder] = array();
		}
		$index = count($this->_buffers[$key][$template][$placeholder]);
		$this->_buffers[$key][$template][$placeholder][$index] = '';
		
		ob_start(array(new Naf_SimpleView_Wrapper($this->_buffers[$key][$template][$placeholder][$index]), 'wrap'));
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

class Naf_SimpleView_Wrapper {
	private $buffer;
	function __construct(& $buffer)
	{
		$this->buffer = & $buffer;
	}
	function wrap($buffer)
	{
		$this->buffer = $buffer;
	}
}