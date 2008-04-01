<?php

/**
 * @deprecated Use of this class is deprecated. Use Naf_Record::create() instead.
 *
 */

class Naf_Record_Registry {
	
	private static $_list = array();
	
	/**
	 * Load record's data from DB
	 *
	 * @param int $id
	 * @return Naf_Record
	 * @throws Naf_Exception_404
	 */
	static function get($class, $id, $notFoundMsg = "Record %d not found")
	{
		if (! array_key_exists($class, self::$_list))
			self::$_list[$class] = array();
		
		if (array_key_exists($id, self::$_list[$class]))
			return self::$_list[$class][$id];
		
		$r = new $class();
		if (! $r->load($id))
			throw new Naf_Exception_404(get_class($rec) . ": " . sprintf($notFoundMsg, $id));
		
		return self::$_list[$class][$id] = $r;
	}
}