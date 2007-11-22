<?php

/**
 * Container for the media parameters.
 * Value-object.
 */

class Naf_Media_Info {
	
	/**
	 * Source movie
	 *
	 * @var string
	 */
	private $filename;
	
	/**
	 * Movie params
	 *
	 * @var mixed
	 */
	private $format, $duration, $bitrate, $audioCodec, $samplingRate, $audioBitrate, 
		$videoCodec, $width, $height, $aspect, $fps;
	
	/**
	 * @var bool
	 */
	private $hasVideo, $hasAudio;
	
	/**
	 * The constructor
	 *
	 * @param string $filename
	 * @throws Naf_Media_Exception
	 */
	function __construct($filename = null)
	{
		if (null === $filename)
			return ;
		else
			$this->setFilename($filename);
	}
	
	/**
	 * @param string $filename
	 * @return Naf_Media_Info
	 */
	function setFilename($filename)
	{
		if ((! is_file($filename)) || ! is_readable($filename))
			throw new Naf_Media_Exception("File unreadable or does not exist: " . $filename);
		
		$this->filename = $filename;
		return $this;
	}
	function getFilename()
	{
		return $this->filename;
	}
	
	/**
	 * @param int | float | string $duration either number of seconds or a time string (HH:MM:SS.F)
	 * @return Naf_Media_Info
	 */
	function setDuration($duration)
	{
		if (is_numeric($duration))
			$this->duration = (float) $duration;
		else
			$this->duration = $this->parseDuration($duration);
		
		$this->duration = round($this->duration, 1);
		return $this;
	}
	/**
	 * @return float duration in seconds
	 */
	function getDuration()
	{
		return $this->duration;
	}
	/**
	 * @return string HH:MM:SS.F
	 */
	function getDurationString()
	{
		$hours = floor($this->duration/3600);
		$minutes = floor(($this->duration - ($hours*3600)) / 60);
		$seconds = $this->duration - ($hours*3600) - ($minutes * 60);
		return sprintf("%02d:%02d:%04.1f", $hours, $minutes, $seconds);
	}
	private function parseDuration($spec)
	{
		if (3 != sscanf($spec, "%d:%d:%f", $hours, $minutes, $seconds))
			throw new Naf_Media_Exception();
		
		return ($hours * 3600) + ($minutes * 60) + $seconds;
	}
	
	/**
	 * @param int $width
	 * @param int $height
	 * @return Naf_Media_Info
	 */
	function setPixelSize($width, $height)
	{
		$width = (int) $width;
		$height = (int) $height;
		
		$this->width = $width;
		$this->height = $height;
		
		$gcd = Naf_Math::gcd($this->width, $this->height);
		$this->aspect = ($this->width/$gcd) . ":" . ($this->height/$gcd);
		return $this;
	}
	/**
	 * @return int
	 */
	function getWidth()
	{
		return $this->width;
	}
	/**
	 * @return int
	 */
	function getHeight()
	{
		return $this->height;
	}
	/**
	 * @return string W:H
	 */
	function getAspect()
	{
		return $this->aspect;
	}
	
	/**
	 * @param bool $has
	 * @return Naf_Media_Info
	 */
	function setHasVideo($has)
	{
		$this->hasVideo = (bool) $has;
		return $this;
	}
	/**
	 * @return bool
	 */
	function hasVideo()
	{
		return $this->hasVideo;
	}
	/**
	 * @param bool $has
	 * @return Naf_Media_Info
	 */
	function setHasAudio($has)
	{
		$this->hasAudio = (bool) $has;
		return $this;
	}
	/**
	 * @return bool
	 */
	function hasAudio()
	{
		return $this->hasAudio;
	}
	
	/**
	 * @param string $format
	 * @return Naf_Media_Info
	 */
	function setFormat($format)
	{
		$this->format = $format;
		return $this;
	}
	/**
	 * @return string
	 */
	function getFormat()
	{
		return $this->format;
	}
	
	/**
	 * @param string $format
	 * @return Naf_Media_Info
	 */
	function setAudioCodec($codecName)
	{
		$this->audioCodec = $codecName;
		return $this;
	}
	/**
	 * @return string
	 */
	function getAudioCodec()
	{
		return $this->audioCodec;
	}
	
	/**
	 * @param string $format
	 * @return Naf_Media_Info
	 */
	function setVideoCodec($codecName)
	{
		$this->videoCodec = $codecName;
		return $this;
	}
	/**
	 * @return string
	 */
	function getVideoCodec()
	{
		return $this->videoCodec;
	}
	
	/**
	 * @param int $bitrate
	 * @return Naf_Media_Info
	 */
	function setBitrate($bitrate)
	{
		$this->bitrate = $bitrate;
		return $this;
	}
	/**
	 * @return int
	 */
	function getBitrate()
	{
		return $this->bitrate;
	}
	
	/**
	 * @param int $bitrate
	 * @return Naf_Media_Info
	 */
	function setAudioBitrate($bitrate)
	{
		$this->audioBitrate = $bitrate;
		return $this;
	}
	/**
	 * @return int
	 */
	function getAudioBitrate()
	{
		return $this->audioBitrate;
	}
	
	/**
	 * @param int $rate
	 * @return Naf_Media_Info
	 */
	function setSamplingRate($rate)
	{
		$this->samplingRate = $rate;
		return $this;
	}
	/**
	 * @return int
	 */
	function getSamplingRate()
	{
		return $this->samplingRate;
	}
	
	/**
	 * @param int $fps
	 * @return Naf_Media_Info
	 */
	function setFps($fps)
	{
		$this->fps = $fps;
		return $this;
	}
	/**
	 * @return int
	 */
	function getFps()
	{
		return $this->fps;
	}
	
}