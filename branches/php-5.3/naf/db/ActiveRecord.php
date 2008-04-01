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

class ActiveRecord {
	/**
	 * @var PDO
	 */
	static protected $connection;
	
	static protected $statementCache = array();
	
	static protected $table, $pk = 'id', $sequence;
	
	static protected $defaults;
	
	static protected $registry = array();
	
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
		return (int) static::$connection->lastInsertId(static::getSequence());
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
	 * @return Record
	 */
	static function find($id, $cols = "*")
	{
		$sql = "SELECT $cols FROM " . (static::$table) . " WHERE id = ?";
		$s = static::statement($sql, $id);
		$s->setFetchMode(PDO::FETCH_CLASS, get_called_class());
		return $s->fetch();
	}
	
	/**
	 * @return Select
	 */
	static function findAll($where = null, $cols = "*")
	{
		$s = new Select(static::$table, $cols);
		return $s->addFilters($where)
			->setConnection(static::$connection)
			->setFetchMode(PDO::FETCH_CLASS, get_called_class());
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
				$this->load($arg);
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
		if ($found = static::find($id))
			return $this->data = $found->export();
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
	
	/**
	 * @return PDOStatement
	 */
	static protected function statement($sql, $data)
	{
		$s = static::$connection->prepare($sql);
		$s->execute((array) $data);
		return $s;
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