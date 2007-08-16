<?php

abstract class InfoReaderTest extends UnitTestCase {
	
	protected $filename;
	
	/**
	 * Expected information values.
	 * Concrete values may differ from one backend to another.
	 */
	protected $format, $duration, $bitrate, $videoCodec, $audioCodec;
	
	function __construct()
	{
		parent::UnitTestCase();
		$this->filename = dirname(__FILE__) . '/sample/test.mpg';
	}
	
	function testCorrectInfoIsRetreived()
	{
		$ir = $this->createReader();
		$i = $ir->info(dirname(__FILE__) . '/sample/test.mpg');
		$this->assertTrue($i instanceof Naf_Media_Info);

		$this->assertTrue($i->hasAudio());
		$this->assertTrue($i->hasVideo());
		$this->assertEqual($this->format, $i->getFormat());
		$this->assertEqual($this->duration, $i->getDuration());
		$this->assertEqual($this->bitrate, $i->getBitrate());
		$this->assertEqual($this->videoCodec, $i->getVideoCodec());
		$this->assertEqual(400, $i->getWidth());
		$this->assertEqual(320, $i->getHeight());
		$this->assertEqual('5:4', $i->getAspect());
		$this->assertEqual(25, $i->getFps());
		$this->assertEqual($this->audioCodec, $i->getAudioCodec());
		$this->assertEqual(44100, $i->getSamplingRate());
		$this->assertEqual(224, $i->getAudioBitrate());
	}
	/**
	 * @return Naf_Media_InfoReader
	 */
	abstract protected function createReader();
}