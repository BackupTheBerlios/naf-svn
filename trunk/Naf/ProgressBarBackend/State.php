<?php

class Naf_ProgressBarBackend_State {
	private $percentDone, $statusString, $elapsed;
	function __construct($percentDone, $statusString, $elapsed)
	{
		$this->percentDone = $percentDone;
		$this->statusString = $statusString;
		$this->elapsed = $elapsed;
	}
	function export()
	{
		return array(
			'percent' => $this->getPercentDone(), 
			'status' => $this->getStatusString(),
			'elapsed' => $this->getElapsedTime());
	}
	function getPercentDone()
	{
		return $this->percentDone;
	}
	function getStatusString()
	{
		return $this->statusString;
	}
	function getElapsedTime()
	{
		return $this->elapsed;
	}
}