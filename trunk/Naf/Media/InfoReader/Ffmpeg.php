<?php

class Naf_Media_InfoReader_Ffmpeg 
	extends Naf_Media_ShellCmdWrapper 
	implements Naf_Media_InfoReader
{
	protected $defaultCommand = 'ffmpeg';
	/**
	 * Read media file information.
	 *
	 * @param string filename
	 * @return Naf_Media_Info
	 */
	function info($filename)
	{
		$i = new Naf_Media_Info($filename);
		
		$cmd = new Naf_ShellCmd($this->command);
		$cmd->addOption('-i', $filename);
		$infoText = $cmd->exec(true);// need to suppress errors due to ffmpeg producing error in case no output file is specified
		if ((! preg_match("~Input\s#0,\s([^,]+)~m", $infoText, $matches1)) || 
			(! preg_match("~\s+Duration\:\s+(\d{2}\:\d{2}\:\-?\d{1,2}\.\d)(?:,\s+start\:\s+\d+\.\d+)?,\s+bitrate\:\s+\-?(\d+|N/A)(?:\s+kb/s)?\s*~m", $infoText, $matches2)))
		{
			throw new Naf_Media_Exception("Unable to read movie info from $infoText");
		}
		
		$i->setFormat($matches1[1]);
		$i->setDuration($matches2[1]);
		
		$bitrate = (int) $matches2[2];
		if (! $bitrate) $bitrate = 200;
		$i->setBitrate($bitrate);
		
		if (preg_match("~.+Audio\:\s+([a-zA-Z0-9\-\_]+),\s+(\d+)\s+Hz(?:,\s+(?:stereo|mono))(?:,\s+[a-zA-Z0-9_\-])?,\s+(\d+)\s+kb/s~m", $infoText, $matches))
		{
			$i->setHasAudio(true);
			$i->setAudioCodec($matches[1]);
			$i->setSamplingRate($matches[2]);
			$i->setAudioBitrate($matches[3]);
		}
		
		if (preg_match("~.+Video\:\s+(\S+)(?:[,/]\s+\S+)?,\s+(\d+)x(\d+)(?:,\s+\d+\s+kb/s)?(?:,\s+(\d+\.\d+)\s+fps\(r\))?~m", $infoText, $matches))
		{
			$i->setHasVideo(true);
			$i->setVideoCodec($matches[1]);
			$i->setPixelSize($matches[2], $matches[3]);
			$i->setFps($matches[4]);
		}
		
		return $i;
	}
}