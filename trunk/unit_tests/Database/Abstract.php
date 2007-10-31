<?php

abstract class AbstractDbTest extends UnitTestCase {
	/**
	 * @var PDO
	 */
	protected $connection;
	
	function __construct()
	{
		parent::__construct();
		
		$c = new Naf_UnitTestDbConnection();
		$this->connection = $c->getConnection();
		Naf_Table::setDefaultConnection($this->connection);
	}
	
	function setUp()
	{
		$this->connection->query("CREATE TABLE test (id INTEGER PRIMARY KEY, name varchar(255))");
	}
	function tearDown()
	{
		$this->connection->query("DROP TABLE test");
	}
}