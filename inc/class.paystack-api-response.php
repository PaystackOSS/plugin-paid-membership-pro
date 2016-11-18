<?php
class ApiResponse{
	function __construct($xmlResponse) {
        $this->lwXml = $xmlResponse;
        if (isset($xmlResponse->E)){
            $this->psError = new LwError($xmlResponse->E->Code, $xmlResponse->E->Msg);
        }
    }
	
	/**
     * lwXml
     * @var SimpleXMLElement
     */
    public $lwXml;
	
	/**
     * psError
     * @var LwError
     */
    public $psError;
	
	/**
     * wallet
     * @var Wallet
     */
    public $wallet;
	
	/**
     * operations
     * @var array Operation
     */
    public $operations;
	
	/**
     * kycDoc
     * @var KycDoc
     */
    public $kycDoc;
	
	/**
     * iban
     * @var Iban
     */
    public $iban;
	
	/**
     * sddMandate
     * @var SddMandate
     */
    public $sddMandate;
}

?>