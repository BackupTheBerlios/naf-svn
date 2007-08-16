<?php

/**
 * Naf and PHPUnit must be within include_path!
 */

define('TEST_ROOT', dirname(__FILE__) . '/');

require_once dirname(__FILE__) . "/config.php";
require_once "Naf.php";
Naf::$settings['autoload_map'] = array('Naf' => '', 'PHPUnit' => '');
Naf_ShellCmd::setTmpDir(TEST_TMP_DIR);