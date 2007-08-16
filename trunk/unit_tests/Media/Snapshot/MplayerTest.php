<?php

require_once dirname(__FILE__) . '/../SnapshotTest.php';

class Naf_Media_Snapshot_Mplayer_Test extends Naf_Media_Snapshot_Test {
	/**
	 * @return Naf_Media_Snapshot
	 */
	protected function createSnapshotMaker($source)
	{
		return new Naf_Media_Snapshot_Mplayer(TEST_MPLAYER, $source, $this->tmpDir());
	}
}