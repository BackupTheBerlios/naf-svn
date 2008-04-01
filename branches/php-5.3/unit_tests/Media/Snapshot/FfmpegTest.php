<?php

require_once dirname(__FILE__) . '/../SnapshotTest.php';

class Naf_Media_Snapshot_Ffmpeg_Test extends Naf_Media_Snapshot_Test {
	/**
	 * @return Naf_Media_Snapshot
	 */
	protected function createSnapshotMaker($source)
	{
		return new Naf_Media_Snapshot_Ffmpeg(TEST_FFMPEG, $source, $this->tmpDir());
	}
}