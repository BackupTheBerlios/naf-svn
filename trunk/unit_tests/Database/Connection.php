<?php

/**
 * A helper for database unit-tests
 */

class Naf_UnitTestDbConnection {
	
	/**
	 * @var PDO
	 */
	private $pdo;
	
	function __construct()
	{
		$this->pdo = new PDO("sqlite:" . dirname(__FILE__) . '/sqlite/db');
	}
	
	function getConnection()
	{
		return $this->pdo;
	}
}