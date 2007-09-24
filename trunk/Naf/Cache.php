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
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		$cache = "";
		foreach (apache_response_headers() as $key => $value)
		{
			if ('set-cookie' != strtolower($key))
				$cache .= $key . ': ' . $value . "\r\n";
		}
		
		$cache .= "\r\n";
		$cache .= ob_get_flush();
		file_put_contents($this->_filename, $cache);
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