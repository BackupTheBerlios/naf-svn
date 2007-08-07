<?php

/**
 * Naf_Record class (Record.php, Wed Jan 10 11:45:30 MSK 2007 11:45:30)
 * 
 * A VERY simple implementation of ActiveRecord design pattern.
 * Actually, any domain/validation logic is intended to be implemented in child-classes.
 */

abstract class Naf_Record {
	
	/**
	 * @var Naf_Table
	 */
	protected $_table;
	
	/**
	 * @var array
	 */
	protected $_data = array();
	
	/**
	 * @var Naf_Validator
	 */
	protected $_validator;
	
	/**#@+
	 * Table specification
	 *
	 * @var mixed
	 */
	protected $_tableName;
	protected $_sequenceName;
	protected $_pk = 'id';
	protected $_defaults;
	/**#@-*/
	
	/**
	 * Callbacks to setup a certain property
	 *
	 * @var callback[]
	 */
	protected $_setters = array();
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->_table = new Naf_Table($this->_tableName, $this->_pk, $this->_sequenceName);
		$this->reset();
	}
	
	/**
	 * @return Naf_Table
	 */
	function table()
	{
		return $this->_table;
	}
	
	function fetchEmpty()
	{
		$empty = $this->_defaults;
		$empty[$this->_pk] = null;
		return $empty;
	}
	
	/**
	 * Import data from array
	 *
	 * @param array $data
	 * @param bool $includeId Whether to import the 'id' element
	 */
	function import(array $data, $includeId = true)
	{
		if ((! $includeId) && array_key_exists($this->_pk, $data))
			unset($data[$this->_pk]);
		
		foreach ($data as $key => $value)
			$this->__set($key, $value);
	}
	
	/**
	 * Export data
	 *
	 * @return array
	 */
	function export()
	{
		return $this->_data;
	}
	
	/**
	 * Save a site
	 *
	 * @param array $row
	 * @return int site ID
	 */
	function save()
	{
		if (! $this->_check()) return false;
		
		$rowData = array_intersect_key($this->_data, $this->_defaults);
		if (empty($this->_data[$this->_pk]))
			return $this->_data[$this->_pk] = $this->_table->insert($rowData);
		else
			return $this->_table->update($rowData, $this->_data[$this->_pk]);
	}
	
	function delete()
	{
		if (empty($this->_data[$this->_pk]))
			return false;
		
		return $this->_table->delete($this->_data[$this->_pk]);
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
		if ($data = $this->_table->find($id))
			return $this->_data = $data;
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
		if ($row = $this->_table->findAll($filter, $order)->fetch())
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
		$this->_data = $this->_defaults;
		$this->_data[$this->_pk] = null;
	}
	
	/**
	 * Get validator for the row to be inserted/updated
	 *
	 * @return Naf_Validator
	 */
	final function validator()
	{
		if (null === $this->_validator)
			$this->_validator = $this->_createValidator();
		
		return $this->_validator;
	}
	
	/**
	 * Create validator for the row to be inserted/updated
	 *
	 * @return Naf_Validator
	 */
	protected function _createValidator()
	{
		return new Naf_Validator();
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
		if (! empty($this->_data[$this->_pk]))
			$where[$this->_tableName . '.' . $this->_pk . ' != ?'] = $this->_data[$this->_pk];

		if ($this->_table->count($where))
			return false;

		return $value;
	}
	
	protected function _check()
	{
		$result = $this->validator()->check($this->_data);
		if ($result->ok())
		{
			$this->_data = $result->export();
			return true;
		}
		else
			return false;
	}
	
	/**
	 * Easy read access to object properties
	 *
	 * @param string $name
	 * @return mixed
	 */
	function __get($name)
	{
		if ('id' == $name) $name = $this->_pk;
		return array_key_exists($name, $this->_data) ? $this->_data[$name] : null;
	}
	
	/**
	 * Easy write access to object properties
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	function __set($name, $value)
	{
		if ('id' == $name) $name = $this->_pk;
		if (array_key_exists($name, $this->_setters))
			$this->_data[$name] = call_user_func(array($this, $this->_setters[$name]), $value);
		else
			$this->_data[$name] = $value;
	}
	
	/**
	 * Frequently used setter. Converts all empty values to NULLs
	 *
	 * @param mixed $value
	 */
	protected function nullEmptyValue($value)
	{
		if (empty($value))
			return null;
		
		return $value;
	}
	
	/**
	 * Easy access to Record's Table methods
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	function __call($method, $args)
	{
		return call_user_func_array(array($this->_table, $method), $args);
	}
}