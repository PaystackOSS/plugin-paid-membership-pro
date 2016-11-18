<?php

class PsError{

	/**
     * CODE number
     * @var string
     */
    public $CODE;
	
	/**
     * MSG error message
     * @var string
     */
    public $MSG;
	
	function __construct($code, $msg) {
		$this->CODE = $code;
		$this->MSG = $msg;
	}
}

?>