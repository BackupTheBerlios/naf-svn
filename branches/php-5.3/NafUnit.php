<?php

class NafUnit {
	private $total, $totalAsserts, $ok, $okAsserts, $failed, $failedAsserts, $messages;
	function setUp()
	{}
	function tearDown()
	{}
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
					
					$this->$m();
					
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
		
		print $this->failed ? "FAILED!" : "OK";
		print PHP_EOL;
		print $this->total . " test methods, {$this->ok} ok, {$this->failed} failed, {$this->okAsserts} correct assertions, {$this->failedAsserts} failed";
		print PHP_EOL;
		print implode(PHP_EOL, $this->messages);
		
	}
	final function assert($expr, $message = null)
	{
		if ($expr)
		{
			$this->okAsserts++;
		} else {
			$this->failedAsserts++;
			$this->messages = $this->message($message ? $message : "Assertion failed");
		}
	}
	protected function message($trace, $preamble = "Failure")
	{
		foreach (debug_backtrace() as $entry)
		{
			if ($this->isTestMethod(@$entry['method']))
			{
				break;
			}
		}
		if (empty($entry['class'])) $entry['class'] = 'Unknown';
		if (empty($entry['method'])) $entry['method'] = 'Unknown';
		if (empty($entry['file'])) $entry['file'] = 'Unknown';
		if (empty($entry['line'])) $entry['line'] = 'Unknown';
		return "$preamble; {$entry['class']}::{$entry['method']}, {$entry['file']} ({$entry['line']})";
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