<?php

/**
 * Unit-test for Naf_Table class
 */

require_once dirname(__FILE__) . '/../setup.php';
require_once dirname(__FILE__) . '/Connection.php';
require_once dirname(__FILE__) . '/Abstract.php';
 
class Naf_Db_TableTest extends AbstractDbTest
{
	const DUMMY_DATA_COUNT = 10;
	
	function testCountAndInsert()
	{
		$t = new Naf_Table('test');
		
		$this->assertEqual(0, $t->count());
		
		$this->assertEqual(1, $t->insert(array('name' => 'test name')));
		$this->assertEqual(1, $t->count());
		
		$row = $t->find(1);
		$this->assertEqual('test name', $row['name']);
	}
	
	function testFindAll()
	{
		$t = $this->loadDummyData();
		
		$all = $t->findAll()->fetchAll();
		$this->assertEqual(self::DUMMY_DATA_COUNT, count($all));
		
		$filtered = $t->findAll(array("name LIKE ?" => 'test1%'))->fetchAll();
		// should select test1 and test10 with this filter
		$this->assertEqual(2, count($filtered));
		
		$limited = $t->findAll(null, null, 5)->fetchAll();
		$this->assertEqual(5, count($limited));
	}
	
	function testFetchPairs()
	{
		$t = $this->loadDummyData();
		$limited = $t->fetchPairs('name');
		$this->assertEqual(self::DUMMY_DATA_COUNT, count($limited));
		foreach ($limited as $id => $name) {
			$this->assertTrue($id > 0);
			$this->assertTrue(is_string($name));
		}
	}
	
	function testCount()
	{
		$t = $this->loadDummyData();
		// should select test1 and test10 with this filter
		$this->assertEqual(2, $t->count(array("name LIKE ?" => 'test1%')));
	}
	
	function testSum()
	{
		$t = $this->loadDummyData();
		// should select id=1 and id=2 with this filter
		$this->assertEqual(2+1, $t->sum('id', array("id <= ?" => 2)));
	}
	
	function testUpdate()
	{
		$t = $this->loadDummyData();
		
		$this->assertEqual(1, $t->update(array('name' => 'updated'), 1));
		
		$row = $t->find(1);
		$this->assertEqual('updated', $row['name']);
	}
	
	function testUpdateAll()
	{
		$t = $this->loadDummyData();
		
		// we'll update a record with a certain ID, so the number of updated rows should be 1
		$this->assertEqual(1, $t->updateAll(array('name' => 'updated'), array('id = ?' => 1)));
		
		$row = $t->find(1);
		$this->assertEqual('updated', $row['name']);
	}
	
	function testDelete()
	{
		$t = $this->loadDummyData();
		$this->assertEqual(1, $t->delete(1));
		$this->assertEqual(self::DUMMY_DATA_COUNT - 1, $t->count());
	}
	
	function testDeleteAll()
	{
		$t = $this->loadDummyData();
		$this->assertEqual(1, $t->deleteAll(array('id = ?' => 1)));
		$this->assertEqual(self::DUMMY_DATA_COUNT - 1, $t->count());
	}
	
	function setUp()
	{
		$this->connection->query("CREATE TABLE test (id INTEGER PRIMARY KEY, name varchar(255))");
	}
	function tearDown()
	{
		$this->connection->query("DROP TABLE test");
	}
	/**
	 * @return Naf_Table
	 */
	private function loadDummyData()
	{
		$t = new Naf_Table('test');
		
		for ($i = 1; $i <= self::DUMMY_DATA_COUNT; ++$i)
		{
			$t->insert(array('name' => 'test' . $i));
		}
		
		return $t;
	}
}