<?php

/**
 * For long-lasting tasks, that the user invokes using Web-interface,
 * it might be useful for the user to stay informed about the state
 * of the task that is being performed (then, at least, the user will know that the
 * process is not hung up)
 *
 * Naf_ProgressBarBackend is a simple backend for task-state monitor.
 */

class Naf_ProgressBarBackend {
	/**
	 * @var string
	 */
	private $taskId;
	/**
	 * @var string
	 */
	private $tmpdir = '/tmp';
	/**
	 * @var true if 
	 */
	private $isWriter;
	private $start;
	
	function __construct($taskId)
	{
		$this->taskId = $taskId;
		$this->start = time();
	}
	
	/**
	 * @return Naf_ProgressBarBackend_State
	 */
	function getState()
	{
		if (is_file($f = $this->filename()))
		{
			$spec = explode("||", file_get_contents($f), 3);
		} else {
			$spec = array(100, '', 10);// seems like task is already completed.
		}
		return new Naf_ProgressBarBackend_State($spec[0], $spec[1], $spec[2]);
	}
	
	/**
	 * @param int $percentDone
	 * @param string $statusString
	 */
	function updateState($percentDone, $statusString)
	{
		$this->isWriter = true;
		file_put_contents($this->filename(), $percentDone . '||' . $statusString . '||' . (time() - $this->start));
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