<?php

class Naf_Record_Registry {
	
	private static $_list = array();
	
	/**
	 * Load record's data from DB
	 *
	 * @param int $id
	 * @return Naf_Record
	 */
	static function get($class, $id)
	{
		if (! array_key_exists($class, self::$_list))
			self::$_list[$class] = array();
		
		if (array_key_exists($id, self::$_list[$class]))
			return self::$_list[$class][$id];
		
		$r = new $class();
		if (! $r->load($id))
			throw new Naf_Record_Registry_Exception('Cannot find ' . $class . ' #' . $id);
		
		return self::$_list[$class][$id] = $r;
	}
}

class Naf_Record_Registry_Exception extends Exception {
	
}