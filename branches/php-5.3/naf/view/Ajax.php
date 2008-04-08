<?php

namespace naf::view;

class Ajax {
	private $data;
	private $forceAjaxResponse;
	function __construct($data, $forceAjaxResponse = false)
	{
		$this->data = $data;
		$this->forceAjaxResponse = (bool) $forceAjaxResponse;
	}
	function render()
	{
		$result = array(
			'code' => 'error',
			'error_list' => array(),
			'data' => $this->data
		);
		
		/**
		 * @todo respect charset settings
		 */
		
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $this->forceAjaxResponse)
		{// we are really ajax OR should be ajax anyway
			echo json_encode($result);
			exit();
		}
		?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Data processed successfully</title>
</head>
<body>
	<h1>Data processed successfully</h1>
	
	<hr />
	
	<p>Press the &quot;Back&quot; button in your browser to return to the form</p>
	
</body>
</html><?php
		
	}
}