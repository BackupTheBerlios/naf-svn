<?php

class Naf_Response {
	
	protected $_view;
	protected $_data = array();
	protected $_title;
	protected $_keywords = array();
	protected $_description = array();
	protected $_status = '200 OK';
	protected $_contentType = 'text/html';
	protected $_charset = 'utf-8';
	protected $_language = 'en';
	protected $_lastModified;
	
	function setView($view)
	{
		$this->_view = $view;
	}
	
	function getView()
	{
		return (null === $this->_view) ? Naf::currentAction() : $this->_view;
	}
	
	function setTitle($title)
	{
		$this->_title = $title;
	}
	
	function getTitle()
	{
		return $this->_title;
	}
	
	function addKeywords($keywords)
	{
		foreach ((array) $keywords as $word)
		{
			$this->_keywords[] = $word;
		}
	}
	
	function getKeywords($asString = false)
	{
		if ($asString)
			return implode(', ', $this->_keywords);
		
		return $this->_keywords;
	}
	
	function setDescription($description)
	{
		$this->_description = $description;
	}
	
	function getDescription()
	{
		return $this->_description;
	}
	
	function setContentType($contentType)
	{
		$this->_contentType = $contentType;
	}
	
	function getContentType()
	{
		return $this->_contentType;
	}
	
	function setLanguage($language)
	{
		$this->_language = $language;
	}
	
	function getLanguage()
	{
		return $this->_language;
	}
	
	function setStatus($status)
	{
		$this->_status = $status;
	}
	
	function exposeStatus()
	{
		header("HTTP/1.0 " . $this->_status);
	}
	
	function setCharset($charset)
	{
		$this->_charset = $charset;
	}
	
	function getCharset()
	{
		return $this->_charset;
	}
	
	function setLastModified($datetime)
	{
		if (($time = strtotime($datetime)) > $this->_lastModified)
			$this->_lastModified = $time;
	}
	
	function exposeContentType()
	{
		header("Content-Type: " . $this->_contentType . "; charset=" . $this->_charset);
	}
	
	function exposeLanguage()
	{
		header("Content-Language: " . $this->_language);
	}
	
	function exposeLastModified()
	{
		if (null === $this->_lastModified) return;
		header("Last-Modified: " . gmdate('D, d M Y H:i:s', $this->_lastModified) . " GMT");
	}
	
	function setAjaxResponse($errorList, $data = null)
	{
		$this->_data['ajax'] = array(
			'errorList' => $errorList,
			'data' => $data);
	}
	
	function __get($name)
	{
		if (array_key_exists($name, $this->_data))
			return $this->_data[$name];
	}
	
	function __set($name, $value)
	{
		$this->_data[$name] = $value;
	}
	
	function export()
	{
		return $this->_data;
	}
}