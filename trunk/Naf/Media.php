<?php

/**
 * Class to work with media files (video/audio)
 */

class Naf_Media {
	
	/**
	 * Backend to use for reader (i.e. ffmpeg or mplayer or smth)
	 *
	 * @var string
	 */
	private static $readerBackend;
	
	/**
	 * @var string
	 */
	private static $readerBackendCommand;
	
	/**
	 * @var Naf_Media_Info
	 */
	private $info;
	
	/**
	 * The constructor
	 *
	 * @param string $filename
	 */
	function __construct($filename)
	{
		if ((! is_file($filename)) || ! is_readable($filename))
			throw new Naf_Media_Exception("File unreadable or does not exist");
		
		$this->filename = $filename;
	}
	
	/**
	 * @return Naf_Media_InfoReader
	 */
	static function reader()
	{
		$class = 'Naf_Media_InfoReader_' . self::$readerBackend;
		if (class_exists($class, false) || is_file(dirname(__FILE__) . '/Media/InfoReader/' . self::$readerBackend . '.php'))
			return new $class(self::$readerBackendCommand);
		
		throw new Naf_Media_Exception("Cannot initialize media-information reader $class");
	}
	
	static function setReaderBackend($backend)
	{
		// prepare $backend string to be used in a class name
		self::$readerBackend = self::sanitizeBackend($backend);
	}
	
	static function setReaderBackendCommand($command)
	{
		self::$readerBackendCommand = $command;
	}
	
	static private function sanitizeBackend($backend)
	{
		return ucfirst(strtolower(trim($backend)));
	}
}