<?php

class Naf_Media_Converter_Mencoder_Avi extends Naf_Media_Converter_Mencoder_Format {
	/**
	 * Configure shell-command
	 *
	 * @param Naf_ShellCmd $c
	 */
	function configure(Naf_ShellCmd $c)
	{
		$f = $this->info->getFormat();
		$c->addOptionIf($f, '-of', $f);
		$c->addOption('-ovc', $this->info->getVideoCodec());
		$c->addOption('-oac', $this->info->getAudioCodec());
	}
}