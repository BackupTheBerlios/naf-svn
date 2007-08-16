<?php

/**
 * Here, we use a test movie, with known parameters:
 * 
 * $ ffmpeg -i ./sample/test.mpg
 * FFmpeg version SVN-r9870, Copyright (c) 2000-2007 Fabrice Bellard, et al.
 *   configuration: --enable-libmp3lame
 *   libavutil version: 49.4.1
 *   libavcodec version: 51.40.4
 *   libavformat version: 51.12.1
 *   built on Aug 13 2007 15:23:18, gcc: 4.1.2 (Ubuntu 4.1.2-0ubuntu4)
 * Input #0, mpeg, from '../sample/test.mpg':
 *   Duration: 00:00:46.9, start: 0.706667, bitrate: 1438 kb/s
 *   Stream #0.0[0x1e0]: Video: mpeg1video, yuv420p, 400x320, 104857 kb/s, 25.00 fps(r)
 *   Stream #0.1[0x1c0]: Audio: mp2, 44100 Hz, stereo, 224 kb/s
 */

require_once dirname(__FILE__) . "/../../setup.php";
require_once dirname(__FILE__) . "/../InfoReaderTest.php";

class Naf_Media_InfoReader_Ffmpeg_Test extends InfoReaderTest {
	
	protected $format = 'mpeg', $duration = 46.9, $bitrate = 1438, $videoCodec = 'mpeg1video', $audioCodec = 'mp2';
	
	protected function createReader()
	{
		return new Naf_Media_InfoReader_Ffmpeg(TEST_FFMPEG);
	}
}