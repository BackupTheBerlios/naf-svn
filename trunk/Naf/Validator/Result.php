<?php

/**
 * Encapsulates validation result
 */
class Naf_Validator_Result {
	
	/**
	 * Collected errors
	 *
	 * @var array
	 */
	protected $_errors = array();
	
	/**
	 * Validated/filtered data
	 *
	 * @var array
	 */
	protected $_data;
	
	/**
	 * Check whether result contains errors
	 *
	 * @return bool TRUE if there were no errors collected, FALSE otherwise
	 */
	function ok()
	{
		return 0 == count($this->_errors);
	}
	
	/**
	 * An opposite to ok()
	 */
	function isError()
	{
		return 0 != count($this->_errors);
	}
	
	function reset()
	{
		$this->_data = null;
		$this->_errors = array();
	}
	
	function addError($error)
	{
		$this->_errors[] = $error;
		return $this;
	}
	
	function getErrorList()
	{
		return $this->_errors;
	}
	
	function import($data)
	{
		$this->_data = $data;
	}
	
	function export()
	{
		return (array) $this->_data;
	}
}