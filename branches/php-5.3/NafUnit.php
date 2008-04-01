<?php

/**
 * This is a simple unit-testing framework.
 *
 * $Id$
 */

class NafUnit {
	
	private $total, $totalAsserts, $ok, $okAsserts, $failed, $failedAsserts, $messages;
	
	private $filename;
	
	final function __construct()
	{
		$r = new ReflectionClass($this);
		$this->filename = $r->getFileName();
	}
	
	function setUp()
	{}
	
	function tearDown()
	{}
	/**
	 * runs the testcase
	 */
	final function run()
	{
		$this->total = $this->ok = $this->failed = $this->totalAsserts = $this->okAsserts = $this->failedAsserts = 0;
		$this->messages = array();
		foreach (get_class_methods($this) as $m)
		{
			if ($this->isTestMethod($m))
			{
				++$this->total;
				try {
					
					$failedBefore = $this->failedAsserts;
					
					$this->setUp();
					
					$this->$m();
					
					$this->tearDown();
					
					if ($this->failedAsserts > $failedBefore)
					{
						$this->failed++;
					} else {
						$this->ok++;
					}
					
				} catch (Exception $e) {
					$this->fail(get_class($e) . ' with message ' . $e->getMessage() . PHP_EOL .
						implode(PHP_EOL . "\t", explode(PHP_EOL, $e->getTraceAsString())));
				}
			}
		}
		
		if (! $this->failed)
		{
			$this->ok = 'all';
		}
		
		print $this->failed ? "FAILED!" : "OK";
		print " - " . get_class($this);
		print PHP_EOL;
		print $this->total . " test methods, {$this->ok} ok";
		if ($this->failed) {
			print ", {$this->failed} failed";
		}
		print ", {$this->okAsserts} correct assertions";
		if ($this->failed) {
			print ", {$this->failed}, {$this->failedAsserts} failed";
		}
		print PHP_EOL;
		print implode(PHP_EOL, $this->messages);
		print PHP_EOL;
		
	}
	
	final function assert($expr, $message = null)
	{
		if ($expr)
		{
			$this->okAsserts++;
		} else {
			$this->failedAsserts++;
			if (! $message) $message = "Assertion failed";
			$this->messages[] = $this->message($message);
		}
	}
	
	final protected function message($preamble = "Failure")
	{
		foreach (debug_backtrace() as $entry)
		{
			if (($entry['file'] == $this->filename))
			{
				break;
			}
		}
		if (empty($entry['function'])) $entry['function'] = 'Unknown';
		if (empty($entry['file'])) $entry['file'] = 'Unknown';
		if (empty($entry['line'])) $entry['line'] = 'Unknown';
		return "$preamble; {$entry['function']}, {$entry['file']} ({$entry['line']})";
	}
	
	final private function isTestMethod($m)
	{
		return 0 === strpos($m, 'test');
	}
	
	final function fail($message)
	{
		$this->failedAsserts++;
		$this->messages[] = $message;
	}
}