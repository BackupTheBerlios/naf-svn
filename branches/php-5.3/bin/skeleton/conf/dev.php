<?php

$settings = array(
	'database' => {database}, 
	'acl' => array(), 
	'autoload_map' => array(), 
	'catchers' => array(
		'Naf_Exception_404' => '404',
		'Naf_Exception_403' => '403',
		'Naf_Exception_401' => '401',
		'Exception' => 'exception/dev')
);

{include_common_config}