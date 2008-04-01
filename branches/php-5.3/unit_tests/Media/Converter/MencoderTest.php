<?php

require_once dirname(__FILE__) . '/../ConverterTest.php';

class Naf_Media_Converter_Mencoder_Test extends ConverterTest {
	function createConverter()
	{
		return new Naf_Media_Converter_Mencoder(TEST_MENCODER, $this->source);
	}
	protected function setUpNafMedia()
	{
		Naf_Media::setReaderBackend('mplayer');
		Naf_Media::setReaderBackendCommand(TEST_MPLAYER);
	}
}