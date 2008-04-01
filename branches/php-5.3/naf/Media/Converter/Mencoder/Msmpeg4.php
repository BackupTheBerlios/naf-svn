<?php

class Naf_Media_Converter_Mencoder_Msmpeg4 extends Naf_Media_Converter_Mencoder_Format {
	/**
	 * Configure shell-command
	 *
	 * @param Naf_ShellCmd $c
	 */
	function configure(Naf_ShellCmd $c)
	{
		$c->addOption('-ovc', 'lavc');
		
		$vbitrate = $this->info->getBitrate();
		if (! $vbitrate) $vbitrate = 64;// 64 is default value of ffmpeg. low quality, low size.
		$c->addOption('-lavcopts', 'vcodec=wmv2:vbitrate=' . $vbitrate);
		
		$c->addOption('-oac', $this->info->getAudioCodec());
	}
}