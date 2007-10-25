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
	 * Raw data
	 *
	 * @var array
	 */
	protected $_raw;
	
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
	
	/**
	 * Reset data, raw-data, errors
	 */
	function reset()
	{
		$this->_data = null;
		$this->_errors = array();
	}

	/**
	 * Add an error message
	 *
	 * @param string $error
	 * @return Naf_Validator_Result $this
	 */
	function addError($error)
	{
		$this->_errors[] = $error;
		return $this;
	}
	
	/**
	 * Export error-list
	 *
	 * @return array
	 */
	function getErrorList()
	{
		return $this->_errors;
	}
	
	/**
	 * Import data array
	 *
	 * @param array $data
	 */
	function import($data)
	{
		$this->_data = $data;
	}
	
	/**
	 * Export data
	 *
	 * @return array
	 */
	function export()
	{
		return (array) $this->_data;
	}
	
	/**
	 * Import/export raw (UNfiltered) data - which could be useful in case of an error.
	 * Thanks to Henry <007_id at sbcglobal.net> for suggesting this feature.
	 * 
	 * @param array $rawData
	 */
	function importRaw(array $data)
	{
		$this->_raw = $rawData;
	}
	
	/**
	 * @return array
	 */
	function exportRaw()
	{
		return (array) $this->_raw;
	}
}