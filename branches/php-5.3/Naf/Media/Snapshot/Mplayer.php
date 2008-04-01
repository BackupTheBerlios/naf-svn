<?php

class Naf_Media_Snapshot_Mplayer extends Naf_Media_Snapshot {
	function save($filename, $start = 0)
	{
		$c = new Naf_ShellCmd($this->command);
		$c->setTarget($this->source);
		$c->addOption('-vo', 'jpeg:outdir=' . $this->tmpDir);
		$c->addOption('-nosound');
		$c->addOption('-frames', 2);
		$c->addOptionIf($start, '-ss', $start);
		
		$c->addOptionIf(($this->width && $this->height), '-vf', 'scale=' . $this->width . ':' . $this->height);
		
		try {
			$c->exec();
		} catch (Naf_Exception $e) {
			throw new Naf_Media_Exception("Snapshot failed! " . $e->getMessage());
		}
		
		@unlink($this->tmpDir . '/00000001.jpg');
		$image = $this->tmpDir . '/00000002.jpg';
		if ($filename)
		{
			rename($image, $filename);
			return ;
		} else {
			$return = file_get_contents($image);
			@unlink($image);
			return $return;
		}
	}
}