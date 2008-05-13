<?php

/**
 * ActiveRecord class 
 * 
 * $Id$
 * 
 * A VERY simple implementation of ActiveRecord design pattern.
 * Any domain logic is intended to be implemented in child-classes.
 * However, validation facility is provided by naf::util::Validator.
 */

namespace naf::db;

use naf::util::Validator;
use naf::err::NotFoundError;

class ActiveRecord {
	/**
	 * @var PDO
	 */
	static protected $connection;
	
	static protected $statementCache = array();
	
	static protected $table, $pk = 'id', $sequence;
	
	static protected $defaults;
	
	static protected $registry = array();
	
	static protected $fetchMode = array(PDO::FETCH_ASSOC);
	
	protected $data = array();
	/**
	 * @var naf::util::Validator
	 */
	protected $validator;
	
	/**
	 * Callbacks to setup a certain property
	 *
	 * @var callback[]
	 */
	protected $setters = array();
	
	/**@+
	 * Control over result set fetching.
	 * @return void
	 */
	static function setFetchModeAssoc()
	{
		static::$fetchMode = array(PDO::FETCH_ASSOC);
	}
	static function setFetchModeClass()
	{
		static::$fetchMode = array(PDO::FETCH_CLASS, get_called_class());
	}
	static function setFetchModeInto()
	{
		static::$fetchMode = array(PDO::FETCH_INTO, new get_called_class());
	}
	/**-@*/
	/**
	 * Insert a new row
	 *
	 * @param array $row
	 * @return int new row ID
	 */
	static function insert($row)
	{
		if (isset($row[static::$pk])) unset($row[static::$pk]);
		
		$sql = 'INSERT INTO ' . (static::$table) . ' (' . implode(', ', array_keys($row)) . 
				') VALUES (?' . str_repeat(', ?', count($row) - 1) . ')';
		static::statement($sql, array_values($row));
		return (int) static::getConnection()->lastInsertId(static::getSequence());
	}
	
	/**
	 * Update a row specified by id $id
	 *
	 * @param array $row
	 * @param int $id (optional, may be specified as a $row member)
	 * @return bool
	 */
	static function update($row, $id = null)
	{
		if ((! $id) && ($row[static::$pk]))
		{
			$id = (int) $row[static::$pk];
		}
		
		if (! $id)
		{
			return false;// @todo exception throw?
		}
		
		$updates = array();
		foreach (array_keys($row) as $field)
		{
			$updates[] = $field . ' = ?';
		}
		
		$sql = 'UPDATE ' . (static::$table) . ' SET ' . implode(', ', $updates) . 
				' WHERE ' . (static::$pk) . ' = ?';
		$data = array_values($row);
		$data[] = $id;
		return static::statement($sql, $data)->rowCount() ?
			$id :
			false;
	}
	/**
	 * Delete row specified by $id
	 *
	 * @param int | int[] $id
	 * @return int Number of rows deleted
	 */
	static function deleteRow($id)
	{
		return (bool) static::statement('DELETE FROM ' . (static::$table) . ' WHERE ' . (static::$pk) . ' = ?', $id)->rowCount();
	}
	/**
	 * @param int $id
	 * @param string $cols
	 * @param int | array $fetchMode - per-call fetching mode overriding static::$fetchMode value
	 * @return Record | array depending on $fetchMode and static::$fetchMode values
	 */
	static function find($id, $cols = "*", $fetchMode = null)
	{
		$sql = "SELECT $cols FROM " . (static::$table) . " WHERE id = ?";
		$s = static::statement($sql, $id, $fetchMode);
		return $s->fetch();
	}
	
	/**
	 * @param array $where conditions for the WHERE SQL clause
	 * @param string $cols
	 * @param int | array $fetchMode - per-call fetching mode overriding static::$fetchMode value
	 * @return naf::db::Select
	 */
	static function findAll($where = null, $cols = "*", $fetchMode = null)
	{
		$s = new Select(static::$table, $cols);
		return $s->addFilters($where)
			->setConnection(static::getConnection())
			->setFetchMode($fetchMode ? $fetchMode : static::$fetchMode);
	}
	/**
	 * @return int
	 */
	static function count($where = null, $expr = '*')
	{
		return static::findAll($where)->count($expr);
	}
	
	static function setConnection($c)
	{
		static::$connection = $c;
	}
	
	/**
	 * Constructor
	 * 
	 * @param int | array $arg (optional) either instance ID -
	 * 							to be immediately loaded from DB, 
	 * 							or an array of data to be immediately imported.
	 */
	function __construct($arg = null)
	{
		if (! count($this->data))
		{/* a check for count($this->data) is necessary:
			when a class instance is created inside PDO->fetch() using PDO::FETCH_CLASS, 
			the constructor is called AFTER the properties have been assigned;
			@see http://bugs.php.net/bug.php?id=4371
			@todo remove the check once the bug is fixed */
			$this->reset();
		}
		
		if (null !== $arg)
		{
			if (is_scalar($arg))
			{
				if (! $this->load($arg))
				{
					throw new NotFoundError();
				}
			} else {
				$this->import($arg);
			}
		}
		
		$this->setup();
	}
	
	/**
	 * Dummy default setup.
	 */
	function setup()
	{}
	
	/**
	 * Import data from array
	 *
	 * @param array $data
	 * @param bool $includeId Whether to import the 'id' element
	 */
	function import($data, $includeId = true)
	{
		if ($data instanceof Record)
		{
			$data = $data->export();
		}
		
		if ((! $includeId) && array_key_exists(static::$pk, $data))
		{
			unset($data[static::$pk]);
		}

		foreach ($data as $key => $value)
		{
			$this->__set($key, $value);
		}
	}
	
	/**
	 * Export data
	 *
	 * @return array
	 */
	function export()
	{
		return $this->data;
	}
	
	function json()
	{
		return json_encode($this->data);
	}
	
	/**
	 * Save a row
	 *
	 * @param array $row
	 * @return int new row ID
	 */
	function save()
	{
		if (! $this->_check()) return false;
		
		$rowData = array_intersect_key($this->data, static::$defaults);
		if (empty($this->data[static::$pk]))
			return $this->data[static::$pk] = static::insert($rowData);
		else
			return static::update($rowData, $this->data[static::$pk]);
	}
	
	function delete()
	{
		if (empty($this->data[static::$pk]))
			return false;
		
		return static::deleteRow($this->data[static::$pk]);
	}
	
	/**
	 * Loads data from a table row with id=$id
	 *
	 * @param int $id
	 * @return array row data on success or bool false if no results can be found
	 */
	function load($id)
	{
		$this->reset();
		if ($found = static::find($id, "*", PDO::FETCH_ASSOC))
			return $this->data = $found;
		else
			return false;
	}
	
	/**
	 * Loads data from a table row with $colname=$colvalue
	 *
	 * @param mixed $colname
	 * @param mixed $colvalue
	 * @return array row data on success or bool false if no results can be found
	 */
	function loadByColumn($colname, $colvalue)
	{
		return $this->loadByFilter(array($colname . ' = ?' => $colvalue));
	}
	
	/**
	 * @param array|string $filter
	 */
	function loadByFilter($filter, $order = null) {
		$this->reset();
		if ($row = static::findAll($filter, $order)->export()->fetch())
		{
			$this->import($row);
			return $row;
		}
		else
			return false;
	}
	
	/**
	 * Reset data to defaults
	 */
	function reset()
	{
		$this->data = static::$defaults;
		$this->data[static::$pk] = null;
	}
	
	/**
	 * Easy read access to object properties
	 *
	 * @param string $name
	 * @return mixed
	 */
	function __get($name)
	{
		return array_key_exists($name, $this->data) ? $this->data[$name] : null;
	}
	
	/**
	 * Easy write access to object properties
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	function __set($name, $value)
	{
		if (array_key_exists($name, $this->setters))
			$this->data[$name] = call_user_func(array($this, $this->setters[$name]), $value);
		else
			$this->data[$name] = $value;
	}
	
	/**
	 * Get validator for the row to be inserted/updated
	 *
	 * @return Nafvalidator
	 */
	final function validator()
	{
		if (null === $this->validator)
			$this->validator = $this->_createValidator();
		
		return $this->validator;
	}
	/**
	 * Shortcut for $this->validator()->result()->getErrorList();
	 *
	 */
	final function getErrorList()
	{
		return $this->validator()->result()->getErrorList();
	}
	
	function nullSetter($value)
	{
		if (empty($value))
		{
			return null;
		} else {
			return $value;
		}
	}
	
	function zeroSetter($value)
	{
		if (empty($value))
		{
			return 0;
		} else {
			return $value;
		}
	}
	
	/**
	 * Create validator for the row to be inserted/updated
	 *
	 * @return naf::util::Validator
	 */
	protected function _createValidator()
	{
		return new Validator();
	}
	
	/**
	 * Filter a field to be unique.
	 *
	 * @param string $field
	 * @param mixed $value
	 * @return mixed value of $value if it is unique, bool FALSE otherwise
	 */
	protected function _filterUnique($field, $value)
	{
		$where = array($field . ' = ?' => $value);
		if (! empty($this->data[static::$pk]))
		{
			$where[(static::$table) . '.' . (static::$pk) . ' != ?'] = $this->data[static::$pk];
		}

		if (static::count($where))
		{
			return false;
		}

		return $value;
	}
	
	protected function _check()
	{
		$result = $this->validator()->check($this->data);
		if ($result->ok())
		{
			$this->data = $result->export();
			return true;
		}
		else
			return false;
	}
	
	static function getConnection()
	{
		if (null === static::$connection)
		{
			ActiveRecord::setConnection(::Naf::pdo());
		}
		
		return static::$connection;
	}
	
	/**
	 * @return PDOStatement
	 */
	static protected function statement($sql, $data, $fetchMode = null)
	{
		$s = static::getConnection()->prepare($sql);
		$s->execute((array) $data);
		static::setupFetchMode($s, $fetchMode);
		return $s;
	}
	/**
	 * @param object $s must implement setFetchMode!
	 * @param $fetchMode override statically bound value when needed
	 */
	static protected function setupFetchMode($s, $fetchMode = null)
	{
		if (null === $fetchMode)
		{
			$fetchMode = static::$fetchMode;
		} else {
			$fetchMode = (array) $fetchMode;
		}
		call_user_func_array(array($s, 'setFetchMode'), static::$fetchMode);
	}
	
	static private function getSequence()
	{
		if (static::$sequence)
		{
			return static::$sequence;
		} else {
			return static::$table . '_' . static::$pk . '_seq';
		}
	}
}