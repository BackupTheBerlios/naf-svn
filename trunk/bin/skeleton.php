<?php

require_once dirname(__FILE__) . '/Cli.php';

define('SKELETON_BASE', dirname(__FILE__) . '/skeleton/');

function skeleton_print_usage() {
	echo "Usage: php skeleton.php ";
}

function skeleton_mkfile($path, $contents = null) {
	global $cli, $conditional;
	
	if (array_key_exists($path, $conditional) && ! $conditional[$path]) return ;
	
	if (is_dir($fullpath = $cli->dir . '/' . $path))
	{// create directory
		if (! mkdir($fullpath, 0755, true))
		{
			print "Directory $fullpath not created\n";
			exit(1);
		}
		
		return ;
	}
	// create file
	if (! is_dir($d = $cli->dir . '/' . dirname($path)) && ! mkdir($d, 0755, true))
	{
		print "Directory $d not created\n";
		exit(1);
	}
	
	if (null === $contents)
	{
		$contents = file_get_contents(SKELETON_BASE . $path);
	}
	
	$contents = preg_replace_callback("/\{([a-z0-9_]+)\}/", '_skeleton_mkfile_callback', $contents);
	
	if (! file_put_contents($d . '/' . basename($path), $contents))
	{
		exit(1);
	}
	
	/*echo $d . '/' . basename($path) .":\n";
	echo $contents ."\n---------------------------------------------------------\n\n";*/
	
}

function _skeleton_mkfile_callback($m) {
	global $cli;
	$replacement = $cli->{$m[1]};
	
	if (is_null($replacement)) return "";
	elseif (is_scalar($replacement)) return (string) $replacement;
	else return var_export($replacement, 1);
}

$defaults = array(
	'dir' => getcwd(), 
	'mode' => 'dev',
	'create_common_config' => true,
	'naf_root' => '/usr/local/lib/php/naf/',
	'multilang' => false, 
	'create_cli_dir' => false, 
	'index_controller' => true, 
	'index_view' => true, 
	'create_init_dir' => false, 
	'www_dir' => 'www',
	'controllers_dir' => array('controllers'),
	'views_dir' => array('views'),
	'database' => array(
		'dsn' => 'mysql:dbname=' . basename(getcwd()), 
		'username' => 'root',
		'password' => ''
	)
);

function append_slash($s) {
	return rtrim($s, " /\\") . "/";
}

function setup_database($s) {
	$ret = array();
	foreach (Cli::commaSplit($s) as $item)
	{
		@list($key, $val) = explode("=", $item);
		$ret[$key] = $val;
	}
	return $ret;
}

$cli = new Cli(
	$defaults, 
	array('controllers_dir' => array('Cli', 'commaSplit'), 
		'views_dir' => array('Cli', 'commaSplit'), 
		'naf_root' => 'append_slash', 
		'database' => 'setup_database')
);

$conditional = array(
	'locale' => (bool) $cli->multilang, 
	'cli' => (bool) $cli->create_cli_dir, 
	'init' => (bool) $cli->create_init_dir, 
	'conf/common.php' => (bool) $cli->create_common_config, 
	'conf/common-include.php' => false,
	'index' => false,
	'controllers' => ($cli->controllers_dir == $defaults['controllers_dir']), 
	'views' => ($cli->views_dir == $defaults['views_dir']), 
	'www' => ($cli->www_dir == $defaults['www_dir'])
);

$cli->include_common_config = $cli->create_common_config ? 
	file_get_contents(SKELETON_BASE . 'conf/common-include.php') :
	"";

foreach (new DirectoryIterator(SKELETON_BASE) as $file)
{
	if ($file->isDot()) continue;
	
	if ($file->isDir())
	{
		
		foreach (new DirectoryIterator(SKELETON_BASE . $file->__toString()) as $sub)
		{
			if ($sub->isDot()) continue;
			
			skeleton_mkfile($file->__toString() . '/' . $sub);
		}
		
	} else {
		
		skeleton_mkfile($file->__toString());
		
	}
}