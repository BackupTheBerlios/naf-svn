<?php

class Naf_Util {
	
	static private $mode;
	
	/**
	 * Decode all %uXXXX entities in string
     * String must not contain %XX entities - they are ignored!
     * Original version of this function can be found in JsHttpRequest library by Dmitry Koterov
	 *
	 * @param string $string
	 * @return string
	 */
	static function ucs2decode($string, $mode = '')
	{
		self::$mode = $mode;
		if (strpos($string, '%u') !== false) // improve speed
			return preg_replace_callback('/%u([0-9A-F]{1,4})/si', array(__CLASS__, 'ucs2decodeCallback'), $string);
		else
			return $string;
	}
	
	/**
     * Decode one %uXXXX entity (RE callback).
     */
	static function ucs2decodeCallback($p)
	{
		return mb_convert_encoding(pack('n', hexdec($p[1])), 'UTF-8', 'UCS-2BE');
	}
}