<?php

require_once dirname(__FILE__) . "/../setup.php";

class Naf_Media_Info_Test extends UnitTestCase {
	
	function testExceptionOnInvalidFilename()
	{
		try {
			$i = new Naf_Media_Info('/non-existant-file');
			$this->fail("An exception of class Naf_Media_Exception should be raised");
		} catch (Naf_Media_Exception $e) {
			;
		}
	}
	
	function testSimpleSettersAndGetters()
	{
		$i = new Naf_Media_Info();
		
		$i->setAudioBitrate(200);
		$this->assertEqual(200, $i->getAudioBitrate());
		
		$i->setAudioCodec('mp3');
		$this->assertEqual('mp3', $i->getAudioCodec());
		
		$i->setBitrate(200);
		$this->assertEqual(200, $i->getBitrate());
		
		$i->setFormat('asf');
		$this->assertEqual('asf', $i->getFormat());
		
		$i->setFps(25);
		$this->assertEqual(25, $i->getFps());
		
		$i->setSamplingRate(44100);
		$this->assertEqual(44100, $i->getSamplingRate());
		
		$i->setVideoCodec('mpeg4');
		$this->assertEqual('mpeg4', $i->getVideoCodec());
	}
	
	function testDurationSetterAndGetter()
	{
		$i = new Naf_Media_Info();
		
		$i->setDuration(10);// set number of seconds
		$this->assertEqual(10, $i->getDuration());
		$this->assertEqual('00:00:10.0', $i->getDurationString());
		
		$hours = 1;
		$minutes = 9;
		$seconds = 9.1;
		$time = sprintf("%02d:%02d:%04.1f", $hours, $minutes, $seconds);
		$i->setDuration($time);// set duration string
		$this->assertEqual($hours*3600 + $minutes * 60 + $seconds, $i->getDuration());
		$this->assertEqual($time, $i->getDurationString());
		
		// check for exception throw on illegal tiem string
		try {
			$i->setDuration('illegal');
			$this->fail("Naf_Media_Exception should be raised");
		} catch (Naf_Media_Exception $e) {
			;
		}
	}
	
	function testPixelSizeSetter()
	{
		$i = new Naf_Media_Info();
		$i->setPixelSize(160, 128);
		$this->assertEqual(160, $i->getWidth());
		$this->assertEqual(128, $i->getHeight());
		$this->assertEqual('5:4', $i->getAspect());
		
		try {
			$i->setPixelSize(15, 128);
			$this->fail("Naf_Media_Exception should be raised");
		} catch (Naf_Media_Exception $e) {
			;
		}
	}
}