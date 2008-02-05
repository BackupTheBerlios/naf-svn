<?php

/* Basic setup */
require_once dirname(__FILE__) . '/../setup.php';

{multilang_setup}

{acl_setup}

$controllersPath = ROOT . 'controllers';
$viewsPath = ROOT . 'views';

Naf::run($controllersPath, $viewsPath);