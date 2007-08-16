<?php

abstract class Naf_Media_Snapshot_Test extends UnitTestCase {
	function testSnapshot()
	{
		$source = TEST_ROOT . '/Media/sample/test.mpg';
		$s = $this->createSnapshotMaker($source);

		$this->assertCorrectSaveToFile($s, $source);
		$this->assertCorrectSaveToString($s);
		$this->assertCorrectResize($s);
	}
	
	protected function assertCorrectSaveToFile(Naf_Media_Snapshot $s, $source)
	{
		$this->assertCorrectImageFile($s, $source, null, null);
	}
	
	protected function assertCorrectResize(Naf_Media_Snapshot $s)
	{
		$this->assertCorrectImageFile($s, null, 100, 50);
		$s->setSize(null, null);// remove resizing!
	}
	
	protected function assertCorrectImageFile(Naf_Media_Snapshot $s, $source, $width, $height)
	{
		$destination = $this->tmpDir() . '/test.jpg';
		
		if ($width && $height)// Resize to expected size
			$s->setSize($width, $height);
		
		$s->save($destination);
		
		$this->assertTrue(is_file($destination));
		
		if (is_array($size = @getimagesize($destination)))
		{
			if (! $width || ! $height)
			{// No resize was made, we have to retrieve size from the movie. $source must point to movie file!
				$ir = new Naf_Media_InfoReader_Ffmpeg(TEST_FFMPEG);
				$i = $ir->info($source);
				$width = $i->getWidth();
				$height = $i->getHeight();
			}
			
			$this->assertEqual($size[0], $width, 'Wrong width');
			$this->assertEqual($size[1], $height, 'Wrong height');
			
		} else {
			$this->fail("Cannot read image info from $destination");
		}
		
		@unlink($destination);
	}
	
	protected function assertCorrectSaveToString(Naf_Media_Snapshot $s)
	{
		$image = $s->save(null, 5);
		$ih = imagecreatefromstring($image);
		$this->assertTrue(is_resource($ih));
	}
	
	protected function tmpDir()
	{
		return TEST_ROOT . 'tmp';
	}
	
	/**
	 * @param string $source
	 * @return Naf_Media_Snapshot
	 */
	abstract protected function createSnapshotMaker($source);
}