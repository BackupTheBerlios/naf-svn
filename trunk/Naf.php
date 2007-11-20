<?php

/**
 * NAF is Not A Framework
 * 
 *
 * Naf class is a toolkit and no more than a collection of static methods and variables.
 */

if (! defined('NAF_ROOT'))
	define('NAF_ROOT', dirname(__FILE__) . '/');

/**
 * enable autoload
 *
 * @param string $class
 */
function __autoload($class) {
	if (false === ($p = strpos($class, '_')))
		$libraryName = $class;
	else
		$libraryName = substr($class, 0, $p);
	
	if (isset(Naf::$settings['autoload_map'][$libraryName]))
		$root = Naf::$settings['autoload_map'][$libraryName];
	else
		$root = APP_LIB_ROOT;

	include_once $root . str_replace('_', '/', $class) . '.php';
}

final class Naf {
	
	/**
	 * @var array
	 */
	static $settings;
	
	/**
	 * @var PDO
	 */
	static $pdo;
	
	/**
	 * @var Naf_Response
	 */
	static $response;
	
	/**
	 * Controllers directory paths
	 *
	 * @var array
	 */
	static private $controllersPath;
	
	/**
	 * Views directory paths
	 *
	 * @var array
	 */
	static private $viewsPath = array();
	
	/**
	 * View helpers
	 *
	 * @var array
	 */
	static private $viewHelpers = array();
	
	/**
	 * @var Naf_Cache
	 */
	static private $cache;
	
	/**
	 * @var Naf_ErrorLog_Interface
	 */
	static private $_logger;
	
	/**
	 * Current action
	 *
	 * @var string
	 */
	static private $_action;
	
	/**
	 * @var Naf_Url
	 */
	static private $_urlComposer;
	
	/**
	 * POST request handlers
	 *
	 * @var array
	 */
	static private $_postHandlers = array();
	
	/**
	 * @param string $mode Settings file name (without extension)
	 */
	static function setUp($mode = null)
	{
		if (null === self::$settings)
		{
			include APP_CONF_ROOT . $mode . '.php';
			self::$settings = $settings;
		}
		
		if (empty(self::$settings['autoload_map']))
			self::$settings['autoload_map'] = array();
		
		self::$settings['autoload_map']['Naf'] = NAF_ROOT;
		
		self::$response = new Naf_Response();
		set_error_handler(array(__CLASS__, 'phpErrorHandler'));
		
		self::$_urlComposer = new Naf_Url();
	}
	
	/**
	 * @param string | array $path
	 */
	static function setControllersPath($path)
	{
		self::$controllersPath = array();
		foreach ((array) $path as $dir)
			self::$controllersPath[] = rtrim($dir, '/') . '/';
	}
	
	/**
	 * @param string | array $path
	 */
	static function setViewsPath($path)
	{
		self::$viewsPath = array();
		foreach ((array) $path as $dir)
			self::$viewsPath[] = rtrim($dir, '/') . '/';
	}
	
	/**
	 * Dispatch request. Find appropriate action script, perform action, render output
	 * 
	 * @param string | array $controllersPath
	 */
	static function run($controllersPath = null, $viewsPath = null)
	{
		if (null !== $controllersPath)
			self::setControllersPath($controllersPath);
		
		if (null !== $viewsPath)
			self::setViewsPath($viewsPath);
		
		if (null === self::$_action)
			self::$_action = self::resolveAction();
		
		self::perform(self::$_action);
		if (null !== self::$cache)
			self::$cache->store();
	}
	
	static function resolveAction()
	{
		if (isset($_REQUEST['ctrl']) && is_string($_REQUEST['ctrl']))
			return self::_escapeAction($_REQUEST['ctrl']);
		else
			return 'index';
	}
	
	static protected function _escapeAction($name)
	{
		return str_replace('.', '/', str_replace('../', '', trim($name, ' /.')));
	}
	
	static function setAction($action)
	{
		self::$_action = self::_escapeAction($action);
	}
	
	/**
	 * Perform action $action
	 *
	 * @param string $action
	 * @param bool $renderView Whether to render view immediately
	 */
	static function perform($action, $renderView = true)
	{
		$lastAction = null;
		while (true)
		{
			try
			{
				foreach (self::$controllersPath as $dir)
				{
					if (is_file($controllerFilename = $dir . $action . '.php'))
					{
						include $controllerFilename;
						if ($renderView)
						{
							self::render();
						}
						return ;
					}
				}
				
				if ($action == $lastAction)
					die('Action ' . $action . ' not found. Additionally, the exception-handler action could not be found.');
				else
				{
					$lastAction = $action;
					throw new Naf_Exception_404();
				}
			}
			catch (Naf_Exception_Stop $s)
			{
				break;
			}
			catch (Naf_Exception_Forward $f)
			{
				if ($f->replace())
					self::setAction($f->where());
				
				$action = $f->where();
				continue;
			}
			catch (Exception $e)
			{
				if ($action = self::handleException($e))
					continue;
			}
		}
	}
	
	/**
	 * @param Exception $e
	 * @access private
	 */
	static private function handleException(Exception $e)
	{
		foreach ((array) @self::$settings['catchers'] as $class => $action)
		{
			if ($e instanceof $class)
			{
				self::$response->exception = $e;
				return self::$_action = $action;
			}
		}
		
		self::handleUncaughtException($e);
		return false;
	}
	
	/**
	 * Forward to another action
	 *
	 * @param string $action
	 * @throws Naf_Exception_Forward
	 */
	static function forward($action, $replaceAction = false)
	{
		throw new Naf_Exception_Forward($action, $replaceAction);
	}
	
	/**
	 * Stop further action executing. Only render output.
	 *
	 * @throws Naf_Exception_Stop
	 */
	static function stop()
	{
		throw new Naf_Exception_Stop();
	}
	
	/**
	 * @param string $root
	 * @param int $lifetime
	 * @param string $key
	 */
	static function initCache($root, $key, $lifetime = 3600)
	{
		self::$cache = new Naf_Cache($root, $key, $lifetime);
		if (self::$cache->start())
		{
			echo microtime(true) - self::$response->timerStart;
			exit();
		}
	}
	
	/**
	 * @return Naf_Cache
	 */
	static function cache()
	{
		return self::$cache;
	}
	
	/**
	 * Get the current action
	 *
	 * @return string
	 */
	static function current()
	{
		return self::$_action;
	}
	
	/**
	 * Check whether $action is the current action
	 *
	 * @param string $action
	 * @param int $accuracy max difference in levels
	 * @return bool
	 */
	static function isCurrent($action, $accuracy = 0)
	{
		$action = rtrim($action, './ ') . '/';
		if (0 !== strpos(self::$_action . '/', $action))
			return false;
		elseif (0 == $accuracy)
			return true;
		else
			return $accuracy > (substr_count(self::$_action . '/', '/') - substr_count($action, '/'));
	}
	
	/**
	 * Handle a special request
	 *
	 * @param string $method REQUEST_METHOD
	 * @param string $action Action to be performed
	 * @param string $trigger When set to NULL - ignored. Otherwise, 
	 * 					the action $action will be performed ONLY in the case
	 * 					that $trigger key is present in the appropriate _REQUEST superglobal
	 * 					( $_GET for GET request, $_POST for POST )
	 * @return bool whether the action was performed
	 */
	static function handleSpecialRequest($method, $action, $trigger)
	{
		$method = strtoupper($method);
		if ($method != $_SERVER['REQUEST_METHOD'])
			return false;

		if (null !== $trigger)
		{
			$request = ('GET' == $method) ? $_GET : $_POST;
			if (!isset($request[$trigger]))
				return false;
		}
		
		if ($action)
			self::perform($action);
		
		return true;
	}
	
	/**
	 * Handle a POST request.
	 *
	 * @param string $action
	 * @param string $trigger
	 * @return bool
	 * @see handleSpecialRequest()
	 */
	static function handlePostRequest($action, $trigger = null)
	{
		return self::handleSpecialRequest('POST', $action, $trigger);
	}
	
	/**
	 * Add view helper
	 *
	 * @param mixed $helper
	 */
	static function addHelper($helper)
	{
		self::$viewHelpers[] = $helper;
	}
	
	static function render()
	{
		try {
			
			self::$response->exposeStatus();
			self::$response->exposeContentType();
			self::$response->exposeLanguage();
			self::$response->exposeLastModified();
			$view = new Naf_SimpleView(self::$response);
			$view->setScriptPath(self::$viewsPath);
			$view->registerHelper(self::$viewHelpers);
			$view->render(self::$response->getView());
			
		} catch (Exception $e) {
			
			if ($action = self::handleException($e))
				self::perform($action);
			else
				die($e->getMessage());
		}
	}
	
	/**
	 * Assert request is done using POST method
	 * @param callback $callback called on method mismatch, defaults to exit()
	 */
	static function assertPost($callback = null)
	{
		if ('POST' != $_SERVER['REQUEST_METHOD'])
		{
			if ($callback)
				call_user_func($callback);
			else
				exit();
		}
	}
	
	/**
	 * Register POST request handler.
	 * Shortcut to
	 * if (Naf::handlePostRequest($action, $trigger)
	 * {
	 * 		Naf::$response->setView($view);
	 * 		Naf::stop();// optionally
	 * }
	 *
	 * @param string $action
	 * @param string $trigger
	 * @param string $view
	 * @param bool $stop
	 */
	static function registerPostHandler($action, $trigger = null, $view = 'ajax', $stop = true)
	{
		if (self::handlePostRequest($action, $trigger))
		{
			self::$response->setView($view);
			if ($stop)
				self::stop();
		}
	}
	
	/**
	 * @return Naf_Url
	 */
	static function urlComposer()
	{
		return self::$_urlComposer;
	}
	
	/**
	 * @param string $action If not specified, the current action is used
	 * @param array $params
	 * @return string
	 */
	static function url($action = null, array $params = array(), $separator = '&')
	{
		return self::$_urlComposer->compose($action, $params, $separator);
	}
	
	/**
	 * @param string $action If not specified, the current action is used
	 * @param array $params
	 * @return string
	 */
	static function urlXml($action = null, array $params = array())
	{
		return self::$_urlComposer->compose($action, $params, '&amp;');
	}
	
	/**
	 * @param array $params
	 * @return string
	 */
	static function currentUrl(array $params = array(), $separator = '&')
	{
		return self::$_urlComposer->compose(self::$_action, array_merge($_GET, $params), $separator);
	}
	
	/**
	 * @param array $params
	 * @return string
	 */
	static function currentUrlXml(array $params = array())
	{
		return self::$_urlComposer->compose(self::$_action, array_merge($_GET, $params), '&amp;');
	}
	
	/**
	 * @param string $action If not specified, the current action is used
	 * @param array $params
	 * @return string
	 */
	static function redirect($action = null, array $params = array())
	{
		self::redirectUrl(self::url($action, $params));
	}
	
	/**
	 * @param string $url
	 * @return void
	 */
	static function redirectUrl($url)
	{
		header('Location: ' . $url);
		exit();
	}
	
	/**
	 * @return string
	 */
	static function currentAction()
	{
		return self::$_action;
	}
	
	static function dbConnect()
	{
		if (self::$pdo instanceof PDO)
			return;
		
		try {
			try {
			self::$pdo = new PDO(
				self::$settings['database']['dsn'],
				self::$settings['database']['username'],
				self::$settings['database']['password'],
				self::$settings['database']['options']);
			self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				die($e->getMessage());
			}
		} catch (PDOException $e) {
			die('DB Connection failed');
		}
		
		if (0 === strpos(self::$settings['database']['dsn'], 'mysql'))
		{
			self::$pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			if (! empty(self::$settings['database']['collation']))
				self::$pdo->query('SET NAMES ' . self::$settings['database']['collation']);
		}
		
		if (! empty(self::$settings['database']['upon_connection_queries']))
			foreach (self::$settings['database']['upon_connection_queries'] as $sql)
				self::$pdo->query($sql);
	}
	
	/**
	 * Strip slashes from input (request variables), if magic_quotes are on.
	 *
	 * NOTE: magic_quotes_sybase setting is ignored!
	 */
	static function stripInputSlashes() {
		if (get_magic_quotes_gpc())
		{
			$_GET = self::_recursiveStripSlashes($_GET);
			$_POST = self::_recursiveStripSlashes($_POST);
			$_REQUEST = self::_recursiveStripSlashes($_REQUEST);
			$_COOKIE = self::_recursiveStripSlashes($_COOKIE);
		}
	}
	
	static protected function _recursiveStripSlashes($value)
	{
		return is_array($value) ? array_map(array(__CLASS__, "_recursiveStripSlashes"), $value) : stripslashes($value);
	}
	
	/**
	 * Php errors' handler. Convert errors to exceptions
	 *
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 */
	static function phpErrorHandler($errno, $errstr, $errfile, $errline)
	{
		if ($errno & error_reporting())
		{
			if (! class_exists('Naf_Exception_Php', false))
			{
				__autoload('Naf_Exception_Php');
				if (! class_exists('Naf_Exception_Php', false))
				{
					die("$errstr in $errfile on line $errline");
				}
			}
			throw new Naf_Exception_Php($errstr, $errno);
		}
		else
		{
			return true;
		}
	}
	
	static private function handleUncaughtException(Exception $e)
	{
		self::_logger()->write($e);
		exit();
	}
	
	static function setLogger(Naf_ErrorLog_Interface $logger)
	{
		self::$_logger = $logger;
	}

	/**
	 * Create error logger
	 *
	 * @return Naf_ErrorLog_Interface
	 */
	static private function _logger()
	{
		if (null === self::$_logger)
		{
			$class = empty(self::$settings['logger']) ? 'Naf_ErrorLog_Null' : self::$settings['logger'];
			self::$_logger = new $class();
		}
		
		return self::$_logger;
	}
}
