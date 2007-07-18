<?php

class Naf_DbList {
	/**
	 * @var Naf_Table
	 */
	protected $_table;
	
	/**
	 * @var array
	 */
	protected $_filters = array();
	
	/**
	 * @var array|string
	 */
	protected $_order;
	
	/**
	 * @var array|string
	 */
	protected $_groupBy, $_having;
	
	function __construct($from, $selection = '*')
	{
		$this->_table = new Naf_Table($from);
		$this->_table->setSelection($selection);
	}
	
	/**
	 * @return PDOStatement
	 */
	final function export($pageNumber = null, $pageSize = null) {
		list($sql, $binds) = $this->_table->getSelectSql($this->_filters);
		$this->_appendGroupBy($sql);
		$this->_appendHaving($sql);
		$this->_table->_appendOrder($sql, $this->_order);
		$this->_table->_appendLimit($sql, $pageSize, $pageNumber);
		return $this->_table->_statement($sql, $binds);
	}
	
	final function setGroupBy($groupBy)
	{
		$this->_groupBy = $groupBy;
	}
	
	final function setHaving($having)
	{
		$this->_having = $having;
	}
	
	final function setOrder($order)
	{
		$this->_order = $order;
		return $this;
	}
	
	final function registerFilter($sql, $binds = null) {
		$this->_filters[$sql] = $binds;
		return $this;
	}
	
	final function unregisterFilter($sql) {
		if (isset($this->_filters[$sql])) unset($this->_filters[$sql]);
		return $this;
	}
	
	final private function _appendGroupBy($sql)
	{
		if (null !== $this->_groupBy)
			$sql .= ' GROUP BY ' . implode(', ', (array) $this->_groupBy);
	}
	
	final private function _appendHaving($sql)
	{
		if (null !== $this->_having)
			$sql .= ' HAVING ((' . implode(') AND (', (array) $this->_having) . '))';
	}
}