<?php

class Naf_Table {
	/**#@+
	 * @var string
	 */
	protected $_name;
	protected $_sequence;
	protected $_selection = '*';
	/**#@-*/
	
	function __construct($name, $sequence = null)
	{
		Naf::dbConnect();
		$this->_name = $this->_from = $name;
		
		if (null === $sequence)
			$this->_sequence = $this->_name . '_id_seq';
		else
			$this->_sequence = $sequence;
	}
	
	/**
	 * Setup what to select from DB table
	 *
	 * @param string | array $selection
	 */
	function setSelection($selection)
	{
		$this->_selection = implode(', ', (array) $selection);
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
				' WHERE ' . $this->_name . '.id = ?', 
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
			return new ArrayObject(array());
		
		$sql = $this->_name . '.id IN (?' . str_repeat(', ?', $count - 1) . ')';
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
		$this->setSelection($this->_name . '.id, ' . $this->_name . '.' . $column);
		$output = array();
		
		try {
			foreach ($this->findAll($where, $order) as $row)
				$output[$row['id']] = $row[$column];
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
	 * @param string $column
	 * @param array | string $where
	 * @return int
	 */
	function sum($column, $where = null)
	{
		$sql = 'SELECT SUM(' . $column . ') FROM ' . $this->_name;
		$binds = $this->_appendWhere($sql, $where);
		return $this->_statement($sql, $binds)->fetchColumn();
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
		return (int) Naf::$pdo->lastInsertId($this->_sequence);
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
				' WHERE id = ?';
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
		$sql = 'DELETE FROM ' . $this->_name . ' WHERE id = ?';
		return $this->_statement($sql, array($id))->rowCount();
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
		$stmt = Naf::$pdo->prepare($sql);
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
		
		$sql .= ' ORDER BY ' . implode(', ', (array) $order);
	}
	
	function _appendLimit(&$sql, $pageSize, $pageNumber)
	{
		if (! $pageSize)
			return ;
		
		$sql .= ' LIMIT ' . $pageSize . ' OFFSET ' . (($pageNumber - 1) * $pageSize);
	}
	
	protected function _removeId(&$row)
	{
		if (array_key_exists('id', $row))
		{
			unset($row['id']);
		}
	}
	
	protected function _prepareBooleans(&$row)
	{
		foreach ($row as $key => $value)
			if (is_bool($value))
				$row[$key] = $value ? 'true' : 'false';
	}
}