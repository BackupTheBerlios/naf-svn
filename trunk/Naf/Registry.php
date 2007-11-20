<?php

/**
 * An implementation of Registry pattern.
 */

class Naf_Registry {
	/**
	 * @var array
	 */
	static private $storage = array();
	/**
	 * @param string $name
	 * @param object $object
	 * @throws Naf_Registry_Exception
	 */
	static function put($name, $object)
	{
		if (self::exists($name))
			throw new Naf_Registry_Exception("Object $name already registered");
		else
			self::$storage[$name] = $object;
	}
	/**
	 * @param string $name
	 * @return bool
	 */
	static function exists($name)
	{
		return array_key_exists($name, self::$storage);
	}
	/**
	 * @param string $name
	 * @return object
	 * @throws Naf_Registry_Exception
	 */
	static function get($name)
	{
		var_dump(self::exists($name));
		die(__METHOD__);
		if (self::exists($name))
			return self::$storage[$name];
		else
			throw new Naf_Registry_Exception("Object $name not found in registry");
	}
}