<?php

if (is_file(dirname(__FILE__) . '/setup.override.php'))
{
	include dirname(__FILE__) . '/setup.override.php';
}

if (! defined('APPMODE')) {/* in production mode by default */
	define('APPMODE', 'production');
}

define('ROOT', dirname(__FILE__) . '/');

if (! defined('NAF_ROOT')) {
	define('NAF_ROOT', ROOT . 'naf/');
}
define('APP_CONF_ROOT', ROOT . 'conf/');
define('APP_LIB_ROOT', ROOT . 'lib/');

include NAF_ROOT . 'Naf.php';
Naf::setUp(APPMODE);