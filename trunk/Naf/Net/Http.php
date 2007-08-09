<?php

/**
 * Class encapsulation for retrieving information from a network resource using HTTP.
 * Does not rely on allow_url_fopen, pure-socket-based
 */

class Naf_Net_Http {
	
	/**
	 * Connection parameters
	 *
	 * @var mixed
	 */
	private $url, $host, $path, $username, $password, $port = 80;
	
	/**
	 * @var resource
	 */
	private $socket;
	
	/**
	 * @var array
	 */
	private $responseHeaders;
	
	/**
	 * @var string
	 */
	private $responseFilename;
	
	/**
	 * Constructor
	 *
	 * @param string $url
	 */
	function __construct($url)
	{
		if (! $this->url = filter_var($url, FILTER_VALIDATE_URL))
			throw new Naf_Net_Exception("Illegal URL");
		
		$info = parse_url($this->url);
		$this->host = $info['host'];
		$this->path = '/' . ltrim($info['path'], '/');
		if (! empty($info['query']))
			$this->path .= '?' . $info['query'];
	}
	
	/**
	 * @param string $username
	 * @param string $password
	 */
	function setAuthInfo($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	}
	
	/**
	 * Perform connection, send request headers
	 */
	function connect()
	{
		if (! ($this->socket = @fsockopen($this->host, $this->port)))
			throw new Naf_Net_Exception("Cannot connect to socket");
		
		$headers = "GET {$this->path} HTTP/1.0\r\n";
		$headers .= "Host: {$this->host}\r\n";
		$headers .= "Connection: Close\r\n";
		
		if ($this->username && $this->password)
			$headers .= "Authorization: Basic ".base64_encode($this->username . ":" . $this->password)."\r\n";
		
		$headers .= "\r\n";

		if (false === fputs($this->socket, $headers))
			throw new Naf_Net_Exception("Write to socket failed");
		
		$response = fgets($this->socket);
		if (0 == substr_count($response, "200 OK"))
			throw new Naf_Net_Exception($this->host . " responded with an error: " . $response);
		
		while ($line = fgets($this->socket)) {
			if ("\r\n" == $line)
				break;
			
			list($name, $value) = explode(":", $line, 2);
			$pos = strpos($value, 'filename=');
			if (false !== $pos)
				$this->responseFilename = substr($value, $pos + 9);
			
			$this->responseHeaders[strtolower($name)] = $value;
		}
		
		if (! strlen($this->responseFilename))
			$this->responseFilename = basename($this->url);
	}
	
	/**
	 * @return string
	 */
	function getHost()
	{
		return $this->host;
	}
	
	/**
	 * @return string
	 */
	function getPath()
	{
		return $this->path;
	}
	
	/**
	 * @return string information retrieved from the network resource
	 */
	function getContents()
	{
		$contents = "";
		while ($buffer = fread($this->socket, 1024))
			$contents .= $buffer;
		
		return $contents;
	}
	
	/**
	 * Save contents to a file
	 *
	 * @param string $filename
	 */
	function saveContents($filename)
	{
		if (! ($fp = @fopen($filename, 'w+')))
			throw new Naf_Net_Exception("Cannot open target file " . $filename);
			
		while ($buffer = fread($this->socket, 1024))
			fwrite($fp, $buffer);
	}
	
	/**
	 * @return string
	 */
	function getResponseFilename()
	{
		return $this->responseFilename;
	}
}