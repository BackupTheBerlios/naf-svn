<?php

class Naf_ProgressBarBackend {
	/**
	 * @var string
	 */
	private $taskId;
	private $tmpdir = '/tmp';
	private $isWriter;
	
	function __construct($taskId)
	{
		$this->taskId = $taskId;
	}
	
	/**
	 * @return Naf_ProgressBarBackend_State
	 */
	function getState()
	{
		if (is_file($f = $this->filename()))
		{
			$spec = explode(",", file_get_contents($f), 2);
		} else {
			$spec = array(0, '');
		}
		return new Naf_ProgressBarBackend_State($spec[0], $spec[1]);
	}
	
	/**
	 * @param int $percentDone
	 * @param string $statusString
	 */
	function updateState($percentDone, $statusString)
	{
		$this->isWriter = true;
		file_put_contents($this->filename(), $percentDone . ',' . $statusString);
	}
	
	/**
	 * @param string $dir
	 * @throws Naf_ProgressBarBackend_Exception
	 */
	function setTmpDir($dir)
	{
		if (! is_string($dir))
		{
			throw new Naf_ProgressBarBackend_Exception("Argument 1 is expected to be a string, " . gettype($dir) . " given");
		}
		if (! is_dir($dir))
		{
			throw new Naf_ProgressBarBackend_Exception("$dir is not a directory");
		}
		if (! is_writable($dir))
		{
			throw new Naf_ProgressBarBackend_Exception("$dir is not writable");
		}
		
		$this->tmpdir = rtrim($dir, '/ ');
	}
	
	private function filename()
	{
		return $this->tmpdir . '/' . $this->taskId . '.npbb';
	}
	
	/**
	 * delete the temporary file if necessary
	 */
	function __destruct()
	{
		if ($this->isWriter && is_file($f = $this->filename()))
		{
			@unlink($f);
		}
	}
}