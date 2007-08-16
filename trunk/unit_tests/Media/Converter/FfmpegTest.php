<?php

require_once dirname(__FILE__) . '/../ConverterTest.php';

class Naf_Media_Converter_Ffmpeg_Test extends ConverterTest {
	function createConverter()
	{
		return new Naf_Media_Converter_Ffmpeg(TEST_FFMPEG, $this->source);
	}
	protected function setUpNafMedia()
	{
		Naf_Media::setReaderBackend('ffmpeg');
		Naf_Media::setReaderBackendCommand(TEST_FFMPEG);
	}
}