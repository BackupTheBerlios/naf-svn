<?php

abstract class ConverterTest extends UnitTestCase {
	
	protected $source, $output;
	
	function __construct()
	{
		/* Working with videos is resource- and time-comsumpting, so: */
		set_time_limit(0);
	}
	
	function testConversionToSameFormat()
	{
		$c = $this->createConverter();
		$result = $c->convert($this->output, 0, 10);
		$this->assertIsA($result, 'Naf_Media_Info');
		$this->assertEqual($this->output, $result->getFilename());
		$this->assertUnchangedSize($result);
	}
	
	function testConversionToAvi()
	{
		$this->assertConversion('avi');
	}
	
	function testConversionToFlv()
	{
		$this->assertConversion('flv');
	}
	
	function testConversionToWmv()
	{
		$this->assertConversion('wmv');
	}
	
	function testConversionToAsf()
	{
		$this->assertConversion('asf');
	}
	
	function testConversionToMpg()
	{
		$this->assertConversion('mpg');
	}
	
	function testResize()
	{
		$c = $this->createConverter();
		$c->setPixelSize(160, 128);
		$result = $c->convert($this->output, 0, 10);
		$this->assertIsA($result, 'Naf_Media_Info');
		$this->assertEqual($this->output, $result->getFilename());
		$this->assertEqual(160, $result->getWidth());
		$this->assertEqual(128, $result->getHeight());
	}
	
	protected function assertConversion($format)
	{
		$c = $this->createConverter();
		$c->setFormat($format);
		$result = $c->convert($this->output, 0, 10);
		$this->assertIsA($result, 'Naf_Media_Info');
		$this->assertEqual($this->output, $result->getFilename());
		$this->assertUnchangedSize($result);
	}
	
	protected function assertUnchangedSize(Naf_Media_Info $result)
	{
		$ir = Naf_Media::reader();
		$i = $ir->info($this->source);
		$this->assertEqual($i->getWidth(), $result->getWidth());
		$this->assertEqual($i->getHeight(), $result->getHeight());
	}
	
	function setUp()
	{
		$this->source = TEST_ROOT . 'Media/sample/test.mpg';
		$this->output = TEST_TMP_DIR . 'test.out';
		$this->setUpNafMedia();
	}
	
	function tearDown()
	{
		@unlink($this->output);
	}
	
	/**
	 * @return Naf_Media_Converter
	 */
	abstract protected function createConverter();
	
	abstract protected function setUpNafMedia();
}