<?php

class Naf_ErrorLog_Browser implements Naf_ErrorLog_Interface
{
	/**
	 * Displays exception information
	 */
	function write(Exception $e)
	{
		echo '<h2>' . get_class($e) . ':</h2>';
		echo '<h1>' . $e->getMessage() . '</h1>';
		echo <<<SCRIPT
<script>
function exception_browser_toogle(id) {
	var style = document.getElementById(id).style
	if (style.display == 'block')
		style.display = 'none'
	else
		style.display = 'block'
	
	return false
}
</script>
SCRIPT;
		echo '<ol>';
		
		foreach ($e->getTrace() as $num => $trace)
		{
			$text  = empty($trace['file']) ? 'Unknown file' : $trace['file'];
			$text .= ', ';
			$text .= empty($trace['line']) ? 'Unknown line' : $trace['line'];
			echo '<li>';
			echo '<a href="#" onclick="return exception_browser_toogle(\'m' . $num . '\')" >' . $text . '</a>';
			echo '<pre id="m' . $num . '" style="display:none;">';
			var_dump($trace);
			echo '</pre>';
			echo '</li>';
		}
		
		echo '</ol>';
	}
}