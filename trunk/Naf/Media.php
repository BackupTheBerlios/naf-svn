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
	 * Backend to use for converter (i.e. ffmpeg or mencoder or smth)
	 *
	 * @var string
	 */
	private static $converterBackend;
	
	/**
	 * @var string
	 */
	private static $converterBackendCommand;
	
	/**
	 * Backend to use for snapshot-maker (i.e. ffmpeg or mplayer or smth)
	 *
	 * @var string
	 */
	private static $snapshotBackend;
	
	/**
	 * @var string
	 */
	private static $snapshotBackendCommand;
	
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
	
	/**
	 * @param string $source Source media filename
	 * @return Naf_Media_Converter
	 */
	static function converter($source)
	{
		$class = 'Naf_Media_Converter_' . self::$converterBackend;
		if (class_exists($class, false) || is_file(dirname(__FILE__) . '/Media/Converter/' . self::$converterBackend . '.php'))
			return new $class(self::$converterBackendCommand, $source);
		
		throw new Naf_Media_Exception("Cannot initialize media-information reader $class");
	}
	
	static function setConverterBackend($backend)
	{
		// prepare $backend string to be used in a class name
		self::$converterBackend = self::sanitizeBackend($backend);
	}
	
	static function setConverterBackendCommand($command)
	{
		self::$converterBackendCommand = $command;
	}
	
	/**
	 * @param string $source Source media filename
	 * @param string $tmpDir temporary directory. Not all backends require a temp dir, but it is better to specify one,
	 * 						even if you don't currently intend to switch between various backends.
	 * @return Naf_Media_Snapshot
	 */
	static function snapshot($source, $tmpDir = '/tmp')
	{
		$class = 'Naf_Media_Snapshot_' . self::$snapshotBackend;
		if (class_exists($class, false) || is_file(dirname(__FILE__) . '/Media/Snapshot/' . self::$snapshotBackend . '.php'))
			return new $class(self::$snapshotBackendCommand, $source);
		
		throw new Naf_Media_Exception("Cannot initialize media-information reader $class");
	}
	
	static function setSnapshotBackend($backend)
	{
		// prepare $backend string to be used in a class name
		self::$converterBackend = self::sanitizeBackend($backend);
	}
	
	static function setSnapshotBackendCommand($command)
	{
		self::$converterBackendCommand = $command;
	}
	
	static private function sanitizeBackend($backend)
	{
		return ucfirst(strtolower(trim($backend)));
	}
}