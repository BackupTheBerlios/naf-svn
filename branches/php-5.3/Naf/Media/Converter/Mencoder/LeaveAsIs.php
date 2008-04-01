<?php

class Naf_Media_Converter_Mencoder_LeaveAsIs extends Naf_Media_Converter_Mencoder_Format {
	/**
	 * Configure shell-command.
	 *
	 * @param Naf_ShellCmd $c
	 */
	function configure(Naf_ShellCmd $c)
	{
		$c->addOption('-ovc', $this->info->getVideoCodec());
		$c->addOption('-oac', $this->info->getAudioCodec());
	}
}