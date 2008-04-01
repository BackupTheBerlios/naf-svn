<?php

class Naf_Media_Snapshot_Ffmpeg extends Naf_Media_Snapshot {
	function save($filename, $start = 0)
	{
		$c = new Naf_ShellCmd($this->command);
		$c->addOption('-i', $this->source);
		$c->addOption('-f', 'image2');
		$c->addOption('-vframes', 1);
		$c->addOptionIf($start, '-ss', $start);
		
		$c->addOptionIf(($this->width && $this->height), '-s', $this->width . 'x' . $this->height);
		
		if (null === $filename)
		{
			$c->setTarget('-');// flush picture to STDOUT
			try {
				return $c->exec();
			} catch (Naf_Exception $e) {
				throw new Naf_Media_Exception("Snapshot failed! " . $e->getMessage());
			}
		} else {
			$c->setTarget($filename);// save to file
			try {
				$c->exec();
				return ;
			} catch (Naf_Exception $e) {
				throw new Naf_Media_Exception("Snapshot failed! " . $e->getMessage());
			}
		}
	}
}