<?php

/**
 * naf::db::Select is Naf implementation of QueryObject.
 * 
 * I used to say that there will be no QueryObject in Naf... However, 
 * the concept proved to be quite good - and therefore I have chosen not to
 * remove Naf_DbList from the library but to rename it to Naf_Select - which
 * is obviously a better name.
 *
 */

namespace naf::db;

class Select {
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
	 * paginate result set.
	 *
	 * @todo after paginate() was called, $this->pageNumber & $this->pageSize must be set, 
	 * 		so that they affect export() even if it called with other arguments.
	 * 		??? - question rizes here regarding a behavior that'll be obviuos.
	 * @param int $pageNumber
	 * @param int $pageSize
	 * @return Naf_Pager
	 */
	function paginate($pageNumber = Naf_Pager::AUTORESOLVE, $pageSize = null)
	{
		$p = new Naf_Pager($this->count(), $pageNumber, $pageSize);
		$this->pageNumber = $p->getPageNumber();
		$this->pageSize = $p->getPageSize();
		return $p;
	}
	
	/**
	 * Set selection
	 *
	 * @param string | array $s
	 * @return string old selection
	 */
	function setSelection($s)
	{
		return $this->_table->setSelection($s);
	}
	
	/**
	 * @return PDOStatement
	 */
	function export($pageNumber = null, $pageSize = null) {
		list($sql, $binds) = $this->sql($pageNumber, $pageSize);
		return $this->_table->_statement($sql, $binds);
	}
	
	function sql($pageNumber = null, $pageSize = null, $inlineBoundVars = false)
	{
		list($sql, $binds) = $this->_table->getSelectSql($this->_filters);
		$this->_appendGroupBy($sql);
		$this->_appendHaving($sql);
		$this->_table->_appendOrder($sql, $this->_order);
		$this->_table->_appendLimit($sql, $pageSize, $pageNumber);
		if ($inlineBoundVars)
		{
			foreach ($binds as $b)
			{
				$sql = preg_replace("/\?/", "'$b'", $sql, 1);
			}
			return $sql;
		} else {
			return array($sql, $binds);
		}
	}
	
	function count($column = "*")
	{
		$s = $this->_table->setSelection('COUNT(' . $column . ')');
		list($sql, $binds) = $this->_table->getSelectSql($this->_filters);
		$this->_table->setSelection($s);
		$this->_appendHaving($sql);
		return $this->_table->_statement($sql, $binds)->fetchColumn();
	}
	
	/**
	 * Get [sub]totals for numeric columns
	 *
	 * @param string | array $expressions
	 * @return string | array
	 */
	function sum($expressions)
	{
		return $this->_table->sum($expressions, $this->_filters);
	}
	
	/**
	 * @param string $groupBy
	 * @return Naf_DbList $this
	 */
	function setGroupBy($groupBy)
	{
		$this->_groupBy = $groupBy;
		return $this;
	}
	/**
	 * @param string $having
	 * @return Naf_DbList $this
	 */
	function setHaving($having)
	{
		$this->_having = $having;
		return $this;
	}
	/**
	 * @param string $order
	 * @return Naf_DbList $this
	 */
	function setOrder($order)
	{
		$this->_order = $order;
		return $this;
	}
	/**
	 * @return string | array
	 */
	final function getOrder()
	{
		return $this->_order;
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
			$sql .= ' HAVING (' . implode(') AND (', (array) $this->_having) . ')';
	}
}
