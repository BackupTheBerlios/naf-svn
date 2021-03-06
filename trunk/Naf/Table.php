<?php

class Naf_Table {
	/**#@+
	 * @var string
	 */
	protected $_name;
	protected $_sequence;
	protected $_pk;
	protected $_selection = '*';
	/**#@-*/
	
	/**
	 * @var PDO
	 */
	static protected $defaultConnection;
	
	/**
	 * @var PDO
	 */
	protected $connection;

	
	/**
	 * Constructor
	 *
	 * @param string $name Table name
	 * @param string $pk Primary key column name
	 * @param string $sequence sequence name (leave empty for auto-gen)
	 */
	function __construct($name, $pk = 'id', $sequence = null)
	{
		$this->_name = $this->_from = $name;
		$this->_pk = $pk;
		
		if (null === $sequence)
			$this->_sequence = $this->_name . '_' . $this->_pk . '_seq';
		else
			$this->_sequence = $sequence;
	}

	/**
	 * @param PDO $connection
	 */
	static function setDefaultConnection($connection)
	{
		self::$defaultConnection = $connection;
	}
	/**
	 * @return PDO
	 */
	function getConnection()
	{
		return $this->connection;
	}
	/**
	 * @param PDO $connection
	 */
	function setConnection($connection)
	{
		$this->connection = $connection;
	}
	
	/**
	 * Setup what to select from DB table
	 *
	 * @param string | array $selection
	 */
	function setSelection($selection)
	{
		$oldValue = $this->_selection;
		$this->_selection = implode(', ', (array) $selection);
		return $oldValue;
	}
	
	/**
	 * Find a row by ID
	 *
	 * @param int $id
	 * @return mixed array row or bool FALSE if none found
	 */
	function find($id)
	{
		return $this->_statement('SELECT ' . $this->_selection . 
				' FROM ' . $this->_name . 
				' WHERE ' . $this->_name . '.' . $this->_pk . ' = ?', 
				array((int) $id))->fetch();
	}
	
	/**
	 * Find all rows satisfying conditions in $where
	 *
	 * @param array $where
	 * @param array | string $order
	 * @param int $pageSize
	 * @param int $pageNumber
	 * @return PDOStatement
	 */
	function findAll($where = null, $order = null, $pageSize = null, $pageNumber = 1)
	{
		$sql = 'SELECT ' . $this->_selection . ' FROM ' . $this->_name;
		$binds = $this->_appendWhere($sql, $where);
		
		$this->_appendOrder($sql, $order);
		$this->_appendLimit($sql, $pageSize, $pageNumber);

		return $this->_statement($sql, $binds);
	}
	
	/**
	 * @param array $idList
	 * @param array | string $where
	 * @param array | string $order
	 * @return PDOStatement | ArrayObject
	 */
	function findInIdList(array $idList, $where = null, $order = null)
	{
		if (! ($count = count($idList)))
			return new PDOStatement();
		
		$sql = $this->_name . '.' . $this->_pk . ' IN (?' . str_repeat(', ?', $count - 1) . ')';
		$where = array_merge((array) $where, array($sql => $idList));
		return $this->findAll($where, $order);
	}
	
	/**
	 * Fetch id => column pairs as array
	 *
	 * @param string $column
	 * @param array | string $where
	 * @param array | string $order
	 * @return array
	 */
	function fetchPairs($column, $where = null, $order = null)
	{
		$selection = $this->_selection;
		$this->setSelection($this->_name . '.' . $this->_pk . ', ' . $this->_name . '.' . $column);
		$output = array();
		
		try {
			foreach ($this->findAll($where, $order) as $row)
				$output[$row[$this->_pk]] = $row[$column];
		} catch (Exception $e) {
			$this->setSelection($selection);
			throw $e;
		}
		
		$this->setSelection($selection);
		return $output;
	}
	
	/**
	 * Count all rows satisfying conditions in $where
	 *
	 * @param array | string $where
	 * @return int
	 */
	function count($where = null)
	{
		$sql = 'SELECT COUNT(*) FROM ' . $this->_name;
		$binds = $this->_appendWhere($sql, $where);
		return $this->_statement($sql, $binds)->fetchColumn();
	}
	
	/**
	 * Calculate sum of values in column $column satisfying conditions in $where
	 *
	 * @param string | array $expressions Either a string (SQL expression to be wrapped in SUM() ) or 
	 * 							an array (SQL expression => alias,..)
	 * @param array | string $where
	 * @return int
	 */
	function sum($expressions, $where = null)
	{
		if (is_string($expressions))
		{
			$sql = 'SELECT SUM(' . $expressions . ') FROM ' . $this->_name;
			$binds = $this->_appendWhere($sql, $where);
			return $this->_statement($sql, $binds)->fetchColumn();
		} else {
			$sum = array();
			foreach ($expressions as $expr => $alias)
			{
				$sum[] = "SUM($expr) AS $alias";
			}
			$sql = 'SELECT ' . implode(", ", $sum) . ' FROM ' . $this->_name;
			$binds = $this->_appendWhere($sql, $where);
			return $this->_statement($sql, $binds)->fetch();
		}
	}
	
	/**
	 * Get SQL clauses: SELECT ... FROM ... WHERE ...
	 *
	 * @param array | string $where
	 * @return array ( SQL, BOUNDS )
	 */
	function getSelectSql($where)
	{
		$sql = "SELECT " . $this->_selection . " FROM " . $this->_name;
		$bounds = $this->_appendWhere($sql, $where);
		return array($sql, $bounds);
	}
	
	/**
	 * Insert a new row
	 *
	 * @param array $row
	 * @return int new row ID
	 */
	function insert(array $row)
	{
		$this->_removeId($row);
		$sql = 'INSERT INTO ' . $this->_name . ' (' . implode(', ', array_keys($row)) . 
				') VALUES (?' . str_repeat(', ?', count($row) - 1) . ')';
		$this->_statement($sql, array_values($row));
		return (int) $this->connection->lastInsertId($this->_sequence);
	}
	
	/**
	 * Update a row specified by id $id
	 *
	 * @param array $row
	 * @return int new row ID
	 */
	function update($row, $id)
	{
		$this->_removeId($row);
		$updates = array();
		foreach (array_keys($row) as $field)
			$updates[] = $field . ' = ?';
		
		$sql = 'UPDATE ' . $this->_name . ' SET ' . implode(', ', $updates) . 
				' WHERE ' . $this->_pk . ' = ?';
		$binds = array_values($row);
		$binds[] = $id;
		$this->_statement($sql, $binds);
		return (int) $id;
	}
	
	/**
	 * Update rows specified by $where
	 *
	 * @param array $values
	 * @return int new row ID
	 */
	function updateAll($values, $where)
	{
		$this->_removeId($values);
		$updates = array();
		foreach (array_keys($values) as $field)
			$updates[] = $field . ' = ?';
		
		$sql = 'UPDATE ' . $this->_name . ' SET ' . implode(', ', $updates);
		$binds = $this->_appendWhere($sql, $where);
		return (int) $this->_statement($sql, array_merge(array_values($values), $binds))->rowCount();
	}
	
	/**
	 * Delete row specified by $id
	 *
	 * @param int | int[] $id
	 * @return int Number of rows deleted
	 */
	function delete($id)
	{
		$sql = 'DELETE FROM ' . $this->_name . ' WHERE ' . $this->_pk . ' = ?';
		return $this->_statement($sql, array((int) $id))->rowCount();
	}
	
	/**
	 * Delete rows specified by $where condition
	 *
	 * @param int | int[] $id
	 * @return int Number of rows deleted
	 */
	function deleteAll($where = null)
	{
		$sql = 'DELETE FROM ' . $this->_name;
		$binds = $this->_appendWhere($sql, $where);
		return $this->_statement($sql, $binds)->rowCount();
	}
	
	/**
	 * @param string $sql
	 * @param array $binds
	 * @return PDOStatement
	 */
	function _statement($sql, array $binds)
	{
		$this->ensureConnection();
		$stmt = $this->connection->prepare($sql);
		$this->_prepareBooleans($binds);
		$stmt->execute($binds);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		return $stmt;
	}
	
	function _appendWhere(&$sql, $where)
	{
		if (empty($where))
		{
			return array();
		}
		elseif (is_string($where))
		{
			$sql .= ' WHERE (' . $where . ')';
			return array();
		}
		else
		{
			$binds = $and = array();
			foreach ($where as $condition => $bindsPart)
			{
				if (null === $bindsPart)
					$bindsPart = array(null);
				
				$binds = array_merge($binds, (array) $bindsPart);
				$and[] = $condition;
			}
			
			$sql .= ' WHERE (' . implode(') AND (', $and) . ')';
			return $binds;
		}
	}
	
	function _appendOrder(&$sql, $order)
	{
		if (empty($order))
			return ;
		
		$normalized = array();
		foreach ((array) $order as $key => $val)
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
	
	function _appendLimit(&$sql, $pageSize, $pageNumber)
	{
		if (! $pageSize)
			return ;
		
		$sql .= ' LIMIT ' . $pageSize . ' OFFSET ' . (($pageNumber - 1) * $pageSize);
	}
	
	protected function _removeId(&$row)
	{
		if (array_key_exists($this->_pk, $row))
			unset($row[$this->_pk]);
	}
	
	protected function _prepareBooleans(&$row)
	{
		foreach ($row as $key => $value)
			if (is_bool($value))
				$row[$key] = $value ? 'true' : 'false';
	}
	
	private function ensureConnection()
	{
		if ($this->connection) return ;
		if (self::$defaultConnection)
		{
			$this->connection = self::$defaultConnection;
			return ;
		}
		Naf::dbConnect();
		self::setDefaultConnection(Naf::$pdo);
		$this->connection = self::$defaultConnection;
	}
}
