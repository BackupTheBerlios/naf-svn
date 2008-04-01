<?php

/**
 * A replacement for PDOStatement: to use as a substitute.
 */

class Naf_NullStatement {
	function fetch()
	{
		return false;
	}
	function fetchAll()
	{
		return array();
	}
	function fetchColumn()
	{
		return null;
	}
	function rowCount()
	{
		return 0;
	}
}