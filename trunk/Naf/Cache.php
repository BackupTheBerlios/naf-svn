<?php

/**
 * @todo Comments!!!
 *
 */

class Naf_Cache {
	protected $_filename;
	protected $_lifetime;
	function __construct($root, $key, $lifetime = 3600)
	{
		$this->_lifetime = $lifetime;
		$this->_filename = $root . crc32(serialize($key));
//		@unlink($this->_filename);
	}
	function start()
	{
		if ($this->_upToDate())
		{
			return $this->_restore();
		}
		else
		{
			ob_start();
			return false;
		}
	}
	function store()
	{
		$headers = array();
		$output = ob_get_flush();
		foreach (apache_response_headers() as $key => $value)
		{
			if ('set-cookie' != strtolower($key))
				$headers[] = $key . ': ' . $value;
		}

		file_put_contents($this->_filename, implode("\r\n", $headers) . "\r\n\r\n" . $output);
	}
	
	protected function _upToDate()
	{
		if (! is_file($this->_filename))
			return false;
		
		if ((time() - filemtime($this->_filename)) < $this->_lifetime)
			return true;
		
		unlink($this->_filename);
	}
	
	protected function _restore()
	{
		list($headers, $body) = explode("\r\n\r\n", file_get_contents($this->_filename), 2);
		
		$headers = explode("\r\n", $headers);
		foreach ($headers as $h)
			header($h);
		
		echo $body;
		
		return true;
	}
}