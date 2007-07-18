<?php

class Naf_HttpRequest {
	protected $_host;
	protected $_path;
	protected $_query;
	function __construct($url)
	{
		$url = parse_url($url);
		
		$this->_host = $url['host'];
		
		if (empty($url['path']))
			$this->_path = '/';
		else
			$this->_path = $url['path'];
		
		if (empty($url['query']))
			$this->_query = '';
		else
			$this->_query = '?' . $url['query'];
	}
	
	function post($data)
	{
		$fp = fsockopen($this->_host, 80, $errno, $errstr, 30);
		
		if (!$fp)
			throw new Naf_HttpRequest_Exception($errstr, $errno);
		
		$header = "POST {$this->_path}{$this->_query} HTTP/1.0\r\n";
		$header .= "Host: {$this->_host}\r\n";
		$boundary = "---------------------" . substr(md5(rand(0, 32000)), 0, 10);
		$header .= "Content-type: multipart/form-data, boundary=$boundary\r\n";
		// attach post vars
		$body = "";
		$postData = http_build_query($data, null, '&');
		$postData = explode('&', $postData);
		foreach($postData AS $pair){
			$pair = explode('=', $pair);
			list($index, $value) = $pair;
			$index = urldecode($index);
			$value = urldecode($value);
			$body .="--$boundary\r\n";
			$body .= "Content-Disposition: form-data; name=\"".$index."\"\r\n";
			$body .= "\r\n".$value."\r\n";
			$body .="--$boundary\r\n";
		}
	
		$header .= "Content-length: " . strlen($body) . "\r\n";
		$header .= "Connection: Close\r\n";
		fwrite($fp, $header . "\r\n" . $body);

		$responseHeaders = '';
		$inHeaders = true;
		$responseBody = '';
		while (! feof($fp))
		{
			$str = fgets($fp, 128);
			if ($inHeaders && ("\r\n" == $str))
			{
				$inHeaders = false;
			}
			elseif ($inHeaders)
			{
				$responseHeaders .= $str;
			}
			else
			{
				$responseBody .= $str;
			}
		}
		fclose($fp);

		$responseHeaders = explode("\r\n", $responseHeaders);
		if (preg_match("~HTTP/1\.[01]\s(\d+)\s(.+)~i", @$responseHeaders[0], $matches))
		{
			$statusCode = $matches[1];
			$statusString = $matches[2];
		}
		else
		{
			$statusCode = '404';
			$statusString = 'Not Found';
		}
		
		if (200 != $statusCode)
			throw new Naf_HttpRequest_Exception('HTTP request failed', $statusCode);
		
		return array('status_code' => $statusCode, 
			'status_string' => $statusString, 
			'headers' => $responseHeaders, 
			'body' => $responseBody);
	}
}

class Naf_HttpRequest_Exception extends Exception {
	
}