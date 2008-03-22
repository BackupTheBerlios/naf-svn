<?php

class Naf_ProgressBarBackend_State {
	private $percentDone, $statusString;
	function __construct($percentDone, $statusString)
	{
		$this->percentDone = $percentDone;
		$this->statusString = $statusString;
	}
	function getPercentDone()
	{
		return $this->percentDone;
	}
	function getStatusString()
	{
		return $this->statusString;
	}
}