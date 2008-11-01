<?php

/**
 * Naf is "Not A Framework"
 * 
 * 
 * @copyright Victor Bolshov <crocodile2u@gmail.com>
 * 
 * version $Id$
 */

if (! defined('NAF_ROOT'))
{
	define('NAF_ROOT', dirname(__FILE__));
}

spl_autoload_register(array('Naf', 'autoload'));

class Naf {
	
	/**
	 * @var array
	 */
	static private $settings = array();
	
	/**
	 * Autoloaded libraries a mapped here
	 *
	 * @var array (LIBRARY-NAME => LIBRARY-ROOT-FOLDER)
	 */
	static private $autoload_map = array('naf' => NAF_ROOT);
	/**
	 * Default library root
	 *
	 * @var string
	 */
	static private $defaultLibraryRoot = NAF_ROOT;
	
	/**
	 * @var Naf_Response
	 */
	static private $response;
	
	/**
	 * @var PDO
	 */
	static private $pdo;
	
	/**
	 * parameters that need to persist between requests.
	 * these params will be attached automagically to URLs generated by url(), urlXml() methods
	 *
	 * @var array
	 */
	static private $persistentUrlParams = array();
	
	/**
	 * Load configuration file $filename. It should have $settings variable
	 * of type array in it.
	 * 
	 * @param string $filename
	 */
	static function loadConfig($filename)
	{
		include $filename;
		if (isset($settings))
		{
			self::importConfig($settings);
		}
	}
	static function loadLibraryMap($map)
	{
		self::$autoload_map = array_merge(self::$autoload_map, $map);
	}
	/**
	 * Get the root folder for a certain library that has been registered with loadLibraryMap
	 *
	 * @param string $library_name
	 * @return string or bool FALSE when the library fails to be found
	 */
	static function getLibraryRoot($library_name)
	{
		if (isset(self::$autoload_map[$library_name]))
		{
			return self::$autoload_map[$library_name];
		} else {
			return false;
		}
	}
	/**
	 * @return array
	 */
	static function exportConfig()
	{
		return self::$settings;
	}
	static function importConfig($settings)
	{
		self::$settings = array_merge(self::$settings, $settings);
		if (isset($settings['autoload_map']))
		{
			self::loadLibraryMap($settings['autoload_map']);
		}
	}
	/**
	 * @param string $key
	 * @param mixed $value
	 */
	static function registerPersistentUrlParameter($key, $value = null)
	{
		self::$persistentUrlParams[$key] = (null === $value) ? @$_GET[$key] : $value;
	}
	/**
	 * generate URL
	 *
	 * @param string $path
	 * @param array $params
	 * @param string $separator
	 * @return string
	 */
	static function url($path, $params = array(), $separator = "&")
	{
		$query = http_build_query(array_merge(self::$persistentUrlParams, $params), null, $separator);
		if (strlen($query))
		{
			return $path . '?' . $query;
		} elseif (strlen($path)) {
			return $path;
		} else {
			return '?';
		}
	}
	/**
	 * generate URL for use in XML documents
	 *
	 * @param string $path
	 * @param array $params
	 * @return string
	 */
	static function urlXml($path, $params = array())
	{
		return self::url($path, $params, "&amp;");
	}
	/**
	 * get current URL with some GET variables [optionally] replaced with new values
	 *
	 * @param array $params
	 * @param string $separator
	 * @return string
	 */
	static function currentUrl($params = array(), $separator = "&")
	{
		return self::url("", array_merge($_GET, $params), $separator);
	}
	/**
	 * get current URL for use in XML documents with some GET variables [optionally] replaced with new values
	 *
	 * @param array $params
	 * @return string
	 */
	static function currentUrlXml($params = array())
	{
		return self::currentUrl($params, "&amp;");
	}

	static function setDefaultLibraryRoot($dir)
	{
		self::$defaultLibraryRoot = $dir;
	}
	
	/**
	 * Autoload
	 *
	 * @param string $class
	 * @return bool
	 */
	static function autoload($class)
	{
		$class = str_replace('::', '_', $class);
		if (false === ($p = strpos($class, '_')))
		{
			$libraryName = $class;
		} else {
			$libraryName = substr($class, 0, $p);
		}
		
		if (array_key_exists($libraryName, self::$autoload_map))
		{
			$root = self::$autoload_map[$libraryName];
		} else {
			$root = self::$defaultLibraryRoot;
		}
		
		if (is_file($filename = rtrim($root, "/") . "/" . str_replace('_', '/', $class) . '.php'))
		{
			include_once $filename;
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Get configuration entry
	 *
	 * @param string $name
	 */
	static function config($name)
	{
		return array_key_exists($name, self::$settings) ? self::$settings[$name] : null;
	}
	
	static function errorHandler($errno, $errstr)
	{
		if ($errno & error_reporting())
		{
			throw new Exception("PHP error " . $errstr);
		}
	}
	
	static function exceptionHandler(Exception $exception)
	{
		$handlers = (array) self::config('catchers');
		foreach ($handlers as $exceptionClass => $handlerClass)
		{
			if ($exception instanceof $exceptionClass)
			{
				$handler = new $handlerClass($exception);
				return $handler->run();
			}
		}
	}

	/**
	 * Setup Naf application
	 */
	static function setup()
	{
		set_error_handler(array(__CLASS__, 'errorHandler'));
		//set_exception_handler(array(__CLASS__, 'exceptionHandler'));
		if (isset($_SERVER['SCRIPT_NAME']))
		{
			self::response()->setView(substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '.')));
		}
	}
	
	/**
	 * @return PDO
	 */
	static function pdo()
	{
		if (is_object(self::$pdo))
		{
			return self::$pdo;
		} else {
			// @todo lazy connection w/help of Naf_Proxy
			self::$pdo = new PDO(
				self::$settings['database']['dsn'],
				self::$settings['database']['username'],
				self::$settings['database']['password'],
				array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
			);
			foreach ((array) @self::$settings['database']['startup_queries'] as $sql)
			{
				self::$pdo->exec($sql);
			}
			if ('mysql' == self::$pdo->getAttribute(PDO::ATTR_DRIVER_NAME))
			{
				self::$pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}
			return self::$pdo;
		}
	}
	
	/**
	 * $map is an array with trigger names as keys and post-handlers as elements.
	 * A trigger is a $_POST key that must be present in $_POST for the corresponding action
	 * to happen. Only the first trigger that is met in the $_POST array, is processed,
	 * all the others are ignored.
	 * 
	 * $trigger is also the name of the post-handler class method to be executed.
	 * 
	 * Example: $_POST = array('save' => 'Ok');
	 * the call to LNaf::handlePost(array('save' => 'SomeSaviour')); - will create 
	 * an instance of SomeSaviour, which MUST implement method named 'save'
	 * 
	 * @param array $map (TRIGGER => action,..)
	 */
	static function handlePost($map, $view = 'ajax')
	{
		if ('POST' != $_SERVER['REQUEST_METHOD'])
		{
			return false;
		}
		
		foreach ($map as $trigger => $handler)
		{
			if (array_key_exists($trigger, $_POST))
			{
				if (is_string($handler))
				{
					$handler = new $handler();
				}
				
				$handler->$trigger($_POST);
				self::response()->setView($view);
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Get Response model
	 * 
	 * @return Naf_Response
	 */
	static function response()
	{
		if (is_object(self::$response))
		{
			return self::$response;
		} else {
			return self::$response = new naf::core::Response();
		}
	}
	
	static function forceAjaxResponse()
	{
		self::response()->ajaxResponseForced = true;
		self::response()->setAjaxData(null);
	}
	
}