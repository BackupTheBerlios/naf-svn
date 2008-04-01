<?php

class Naf_Validator_Chain {
	/**
	 * @var SplObjectStorage
	 */
	protected $_validatorStorage;
	
	/**
	 * @var Naf_Validator_Result
	 */
	protected $_result;
	
	function __construct()
	{
		$this->_validatorStorage = new SplObjectStorage();
		foreach (func_get_args() as $o)
			$this->addValidator($o);
		
		$this->_result = new Naf_Validator_Result();
	}
	
	function addValidator(Naf_Validator $validator)
	{
		$this->_validatorStorage->attach($validator);
	}
	
	/**
	 * @return Naf_Validator_Result
	 */
	function check($input)
	{
		foreach ($this->_validatorStorage as $validator)
			if (! $validator->check($input)->ok())
				return $this->_result = $validator->result();
		
		return $this->_result;
	}
	
	/**
	 * @return Naf_Validator_Result
	 */
	function result()
	{
		return $this->_result;
	}
}