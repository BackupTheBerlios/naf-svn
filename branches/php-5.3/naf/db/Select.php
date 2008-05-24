<?php

/**
 * naf::db::Select is (kind of simple, as alwayse ;) ) Naf implementation of M. Fowler's Query-Object design pattern.
 * 
 * $Id$
 */

namespace naf::db;

use ::Naf;// for default DB connection

class Select implements IteratorAggregate, Countable {
	/**
	 * @var PDO
	 */
	private static $defaultConnection;
	/**
	 * @var PDO
	 */
	private $connection;
	protected $fetchMode = array(PDO::FETCH_ASSOC);
	/**
	 * FROM clause spec
	 *
	 * @var string
	 */
	private $from, $selection;
	/**
	 * @var array
	 */
	private $filters = array();
	
	/**
	 * @var array|string
	 */
	private $order;
	
	/**
	 * @var array|string
	 */
	private $groupBy, $having;
	
	/**
	 * @var int
	 */
	private $pageNumber, $pageSize = 20;
	
	function __construct($from, $selection = '*')
	{
		$this->from = $from;
		$this->selection = $selection;
	}
	
	static function setDefaultConnection($c)
	{
		self::$defaultConnection = $c;
	}
	
	function setConnection($c)
	{
		$this->connection = $c;
		return $this;
	}
	
	function setFetchMode($mode, $opts = null)
	{
		$this->fetchMode = is_array($mode) ?
			$mode :
			func_get_args();
		return $this;
	}
	
	/**
	 * paginate result set.
	 * @param Naf_Pager $pager
	 */
	function paginate($pageNumber, $pageSize = null)
	{
		$this->pageNumber = $pageNumber;
		if ($pageSize)
		{
			$this->pageSize = $pageSize;
		}
		return $this;
	}
	
	/**
	 * @return PDOStatement
	 */
	function export() {
		$data = array();
		$sql = $this->baseSQL($data, $this->selection);
		
		$this->_appendGroupBy($sql);
		$this->_appendHaving($sql);
		
		$this->_appendOrder($sql);
		$this->_appendLimit($sql);
		
		return $this->statement($sql, $data);
	}
	
	/**
	 * @return Iterator
	 */
	function getIterator()
	{
		return $this->export();
	}
	
	private function baseSQL(&$data, $selection)
	{
		$sql = "SELECT " . $selection . " FROM " . $this->from;
		$data = $this->_appendWhere($sql, $this->filters);
		return $sql;
	}
	
	function count($column = "*")
	{
		$data = array();
		$sql = $this->baseSQL($data, "COUNT($column)");
		$this->_appendHaving($sql);
		
		return $this->statement($sql, $data)->fetchColumn();
	}
	
	/**
	 * @param string $groupBy
	 * @return Select $this
	 */
	function setGroupBy($groupBy)
	{
		$this->groupBy = $groupBy;
		return $this;
	}
	/**
	 * @param string $having
	 * @return Select $this
	 */
	function setHaving($having)
	{
		$this->having = $having;
		return $this;
	}
	/**
	 * @param string $order
	 * @return Select $this
	 */
	function setOrder($order)
	{
		$this->order = $order;
		return $this;
	}
	/**
	 * @return string | array
	 */
	final function getOrder()
	{
		return $this->order;
	}
	function from($from)
	{
		$this->from = $from;
		return $this;
	}
	function setSelection($selection)
	{
		$this->selection = $selection;
		return $this;
	}
	function where($where, $binds = null)
	{
		if (is_string($where))
		{
			return $this->addFilter($where, $binds);
		} else {
			assert(is_array($where));
			return $this->addFilters($where);
		}
	}
	final function addFilters($filters)
	{
		foreach ((array) $filters as $sql => $data)
		{
			$this->addFilter($sql, $data);
		}
		return $this;
	}
	/**
	 * @param string $sql
	 * @param array $binds
	 * @return Select $this
	 */
	final function addFilter($sql, $binds = null) {
		$this->filters[$sql] = $binds;
		return $this;
	}
	/**
	 * Register filter ONLY IF $condition evaluates to TRUE
	 * 
	 * @param bool $condition
	 * @param string $sql
	 * @param array $binds
	 * @return Select $this
	 */
	final function addFilterIf($condition, $sql, $binds = null) {
		if ($condition)
		{
			$this->filters[$sql] = $binds;
		}
		return $this;
	}
	
	final function removeFilter($sql) {
		if (isset($this->filters[$sql])) unset($this->filters[$sql]);
		return $this;
	}
	
	final private function _appendWhere(&$sql)
	{
		if (! count($this->filters))
		{
			return array();
		}
		$bound_vars = $and = array();
		foreach ($this->filters as $key => $val)
		{
			if (is_string($key))
			{
				$and[] = $key;
				if (null !== $val)
				{
					$val = (array) $val;
				}
				$bound_vars = array_merge($bound_vars, $val);
			} else {
				$and[] = $val;
			}
		}
		
		$sql .= ' WHERE (' . implode(') AND (', $and) . ')';
		return $bound_vars;
	}
	
	final private function _appendGroupBy(&$sql)
	{
		if (null !== $this->groupBy)
			$sql .= ' GROUP BY ' . implode(', ', (array) $this->groupBy);
	}
	
	final private function _appendHaving(&$sql)
	{
		if (null !== $this->having)
			$sql .= ' HAVING (' . implode(') AND (', (array) $this->having) . ')';
	}
	
	final private function _appendOrder(&$sql)
	{
		if (empty($this->order))
			return ;
		
		$normalized = array();
		foreach ((array) $this->order as $key => $val)
		{
			if (is_numeric($key))
			{
				$normalized[] = $val;
			} else {
				$normalized[] = $key . ' ' . $val;
			}
		}
		$sql .= ' ORDER BY ' . implode(', ', $normalized);
	}
	
	final private function _appendLimit(&$sql)
	{
		if (! $this->pageNumber)
		{
			return ;
		}
		
		$sql .= ' LIMIT ' . $this->pageSize . ' OFFSET ' . (($this->pageNumber - 1) * $this->pageSize);
	}
	
	private function statement($sql, $data)
	{
		$s = $this->getConnection()->prepare($sql);
		$s->execute($data);
		call_user_func_array(array($s, 'setFetchMode'), $this->fetchMode);
		return $s;
	}
	/**
	 * @return PDO
	 */
	function getConnection()
	{
		if ($this->connection)
		{
			return $this->connection;
		} elseif (self::$defaultConnection) {
			return self::$defaultConnection;
		} else {
			return Naf::pdo();
		}
	}
}