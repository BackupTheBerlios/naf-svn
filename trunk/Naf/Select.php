<?php

/**
 * Naf_Select is Naf implementation of QueryObject.
 * 
 * I used to say that there will be no QueryObject in Naf... However, 
 * the concept proved to be quite good - and therefore I have chosen not to
 * remove Naf_DbList from the library but to rename it to Naf_Select - which
 * is obviously a better name.
 *
 */

class Naf_Select {
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
	function export($pageNumber = null, $pageSize = null) {
		list($sql, $binds) = $this->_table->getSelectSql($this->_filters);
		$this->_appendGroupBy($sql);
		$this->_appendHaving($sql);
		$this->_table->_appendOrder($sql, $this->_order);
		$this->_table->_appendLimit($sql, $pageSize, $pageNumber);
		return $this->_table->_statement($sql, $binds);
	}
	
	final function count()
	{
		$s = $this->_table->setSelection('COUNT(*)');
		list($sql, $binds) = $this->_table->getSelectSql($this->_filters);
		$this->_table->setSelection($s);
		$this->_appendHaving($sql);
		return $this->_table->_statement($sql, $binds)->fetchColumn();
	}
	
	/**
	 * @param string $groupBy
	 * @return Naf_DbList $this
	 */
	final function setGroupBy($groupBy)
	{
		$this->_groupBy = $groupBy;
		return $this;
	}
	/**
	 * @param string $having
	 * @return Naf_DbList $this
	 */
	final function setHaving($having)
	{
		$this->_having = $having;
		return $this;
	}
	/**
	 * @param string $order
	 * @return Naf_DbList $this
	 */
	final function setOrder($order)
	{
		$this->_order = $order;
		return $this;
	}
	/**
	 * @param string $sql
	 * @param array $binds
	 * @return Naf_DbList $this
	 */
	final function registerFilter($sql, $binds = null) {
		$this->_filters[$sql] = $binds;
		return $this;
	}
	/**
	 * Register filter ONLY IF $condition evaluates to TRUE
	 * 
	 * @param bool $condition
	 * @param string $sql
	 * @param array $binds
	 * @return Naf_DbList $this
	 */
	final function registerFilterIf($condition, $sql, $binds = null) {
		if ($condition)
			$this->_filters[$sql] = $binds;
		return $this;
	}
	
	final function unregisterFilter($sql) {
		if (isset($this->_filters[$sql])) unset($this->_filters[$sql]);
		return $this;
	}
	
	final private function _appendGroupBy(&$sql)
	{
		if (null !== $this->_groupBy)
			$sql .= ' GROUP BY ' . implode(', ', (array) $this->_groupBy);
	}
	
	final private function _appendHaving(&$sql)
	{
		if (null !== $this->_having)
			$sql .= ' HAVING ((' . implode(') AND (', (array) $this->_having) . '))';
	}
}