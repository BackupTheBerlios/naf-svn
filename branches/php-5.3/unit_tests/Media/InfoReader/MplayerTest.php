<?php

/**
 * Here, we use a test movie, with known parameters:
 * 
 * $ mplayer -identify -frames 0 test.mpg
 * Playing test.mpg.
 * ID_VIDEO_ID=0
 * ID_AUDIO_ID=0
 * MPEG-PS file format detected.
 * VIDEO:  MPEG1  400x320  (aspect 1)  25.000 fps    0.0 kbps ( 0.0 kbyte/s)
 * ID_FILENAME=test.mpg
 * ID_DEMUXER=mpegps
 * ID_VIDEO_FORMAT=0x10000001
 * ID_VIDEO_BITRATE=0
 * ID_VIDEO_WIDTH=400
 * ID_VIDEO_HEIGHT=320
 * ID_VIDEO_FPS=25.000
 * ID_VIDEO_ASPECT=0.0000
 * ID_AUDIO_FORMAT=80
 * ID_AUDIO_BITRATE=0
 * ID_AUDIO_RATE=0
 * ID_AUDIO_NCH=0
 * ID_LENGTH=48.87
 * ...
 * ==========================================================================
 * ID_VIDEO_CODEC=mpeg12
 * ==========================================================================
 * ...
 * ==========================================================================
 * AO: [alsa] 48000Hz 2ch s16le (2 bytes per sample)
 * ID_AUDIO_CODEC=mad
 */

require_once dirname(__FILE__) . "/../../setup.php";
require_once dirname(__FILE__) . "/../InfoReaderTest.php";

class Naf_Media_InfoReader_Mplayer_Test extends InfoReaderTest {
	
	protected $format = 'MPEG-PS', $duration = 48.9, $bitrate = 0, $videoCodec = 'mpegpes', $audioCodec = 'mp3';
	
	function __construct()
	{
		parent::__construct();
		$this->bitrate = ((filesize($this->filename) / 1024) * 8) / $this->duration;
	}
	
	protected function createReader()
	{
		return new Naf_Media_InfoReader_Mplayer(TEST_MPLAYER);
	}
}