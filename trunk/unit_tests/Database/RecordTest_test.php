<?php

/**
 * Unit-test for Naf_Record class
 */

require_once dirname(__FILE__) . '/../setup.php';
require_once dirname(__FILE__) . '/Connection.php';
require_once dirname(__FILE__) . '/Abstract.php';

/**
 * Dummy record class - for the test table created by AbstractDbTest
 */
class TestRecord extends Naf_Record {
	protected $_tableName = 'test';
	protected $_defaults = array(
		'name' => null);
		
	protected function _createValidator()
	{
		return parent::_createValidator()
			->addRequired('name', 'Name is required')
			->addStringRule('name', 'Name must be a string')
			->addCallbackRule('name', array($this, 'filterUniqueName'), 'Name must be unique within the database');
	}
	
	function filterUniqueName($name)
	{
		return parent::_filterUnique('name', $name);
	}
}

class Naf_Record_Test extends AbstractDbTest {
	
	function testImportAndExport()
	{
		$r = new TestRecord();
		
		$this->assertEqual($r->export(), array('id' => null, 'name' => null));
		
		$r->import(array('name' => 'test'));
		$this->assertEqual($r->export(), array('id' => null, 'name' => 'test'));
	}
	
	function testSave()
	{
		$r = new TestRecord();
		$r->name = 'test';
		$this->assertSave($r);
		$this->assertEqual(1, $r->table()->count());
	}
	
	function testDelete()
	{
		$r = new TestRecord();
		$r->name = 'test';
		$this->assertSave($r);
		$this->assertTrue($r->delete());
		$this->assertEqual(0, $r->table()->count());
	}
	
	function testLoadMethods()
	{
		$r = new TestRecord();
		$r->name = 'test';
		$id = $this->assertSave($r);
		$r->reset();
		
		$this->assertTrue($r->load($id));
		$r->reset();
		
		$this->assertTrue($r->loadByColumn('name', 'test'));
		$r->reset();
		
		$this->assertTrue($r->loadByFilter(array('name = ?' => 'test')));
		$r->reset();
	}
	
	function testValidation()
	{
		$r = new TestRecord();
		// required rule should fail
		$this->assertFalse($r->save());
		
		// string rule should fail
		$r->name = array();
		$this->assertFalse($r->save());
		
		$r->name = 'test';
		$this->assertTrue($r->save());
		
		$nonUnique = new TestRecord();
		$nonUnique->name = $r->name;
		// unique rule should fail
		$this->assertFalse($nonUnique->save());
		
		$this->assertIsA($nonUnique->validator(), 'Naf_Validator');
		$this->assertTrue(is_array($nonUnique->getErrorList()));
		$this->assertTrue(count($nonUnique->getErrorList()) > 0);
	}
	
	private function assertSave($r)
	{
		$name = $r->name;
		$this->assertTrue($id = $r->save());
		
		$r->reset();
		$this->assertTrue($r->load($id));
		$this->assertEqual($name, $r->name);
		return $id;
	}
	
}