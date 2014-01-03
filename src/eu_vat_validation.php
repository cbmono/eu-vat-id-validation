<?php
/**
 *  European VAT-ID validation (VIES VAT-ID)
 *  URL: http://ec.europa.eu/taxation_customs/vies
 * 
 *  If company name and address are available, these also get extracted
 *  and are accessible after valdiation via EuVatValidation::getVatIdExtended()
 * 
 * 
 *  Example (short):
 *  ---------------------------------
 *  $vatId = new EuVatValidation('DE273616207');
 *  print_r($vatId->isValid());
 * 
 * 
 *  Example (medium - Recommended to validate many VAT-ID's with one class instance):
 *  ---------------------------------
 *  $vatId = new EuVatValidation;
 * 
 *  $vatId->setVatId('DE273616207');
 *  print_r($vatId->isValid());
 * 
 *  $vatId->setVatId('AU374651267');
 *  print_r($vatId->isValid());
 * 
 * 
 *  Example (long):
 *  ---------------------------------
 *  $vatId = new EuVatValidation;
 *  $vatId->setVatId('DE273616207');
 *  $vatId->validate();
 *  print_r($vatId->getVatIdExtended());
 */

class EuVatValidation {

  public $soapClient;

  private $vatId;
  private $wsdlUrl = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
  
  
  /**
   * Constructor
   * If $vatId isn't empty, the validation gets triggered right away
   * 
   * @throws SoapFault
   * @param string $vatId
   */
  public function __construct($vatId = null) {

    try {
      $this->soapClient = new SoapClient($this->wsdlUrl);  
    }
    catch(SoapFault $e) {
      exit('Error establishing SOAP connection: ' . $e->faultstring);
    }    

    if ($vatId !== null) {
      $this->setVatId($vatId);
      $this->validate();
    }
    else {
      $this->resetVatId();
    }
  }


  /**
   * Set VAT-ID and extract VAT-Number and country code
   * 
   * @throws Exception
   * @param string $vatId^
   */
  public function setVatId($vatId) {

    $vatId = $this->clean($vatId);

    if (empty($vatId)) {
      throw new Exception('VAT-ID cannot be empty');
    }

    $this->resetVatId();
    $this->vatId['vatId']       = $vatId;
    $this->vatId['vatNumber']   = $this->getVatNumber();
    $this->vatId['countryCode'] = $this->getCountryCode();
  }


  /**
   * Get current VAT-ID value
   * 
   * @return string
   */
  public function getVatId() {

    return $this->vatId['vatId'];
  }


  /**
   * Get current VAT-ID value and details
   * 
   * @return array
   */
  public function getVatIdExtended() {

    return $this->vatId;
  }


  /**
   * Validates the current VAT-ID value.
   * If available, company name and address are also set
   */
  public function validate() {

    $response = $this->sendValidationRequest();

    $this->vatId['isValid'] = $response->valid;
    $this->vatId['companyName'] = $response->name !== '---' ? $response->name : null;
    $this->vatId['companyAddress'] = $response->address !== '---' ? $response->address : null;
  }


  /**
   * Get the current value of $this->vatId['isValid'].
   * If $this->vatId['isValid'] is still NULL, the current VAT-ID gets validated before checking $this->vatId['isValid']
   * 
   * @return boolean
   */
  public function isValid() {

    if ($this->vatId['isValid'] === null) {
      $this->validate();
    }

    return $this->vatId['isValid'];
  }


  /**
   * Reset $vatId with its default values
   */
  private function resetVatId() {

    $this->vatId = array(
      'vatId'           => null,
      'vatNumber'       => null,
      'countryCode'     => null,
      'isValid'         => null,
      'companyName'     => null,
      'companyAddress'  => null
    );
  }


  /**
   * Clean a string removing spaces, comas, etc.
   * 
   * @param string $str
   * @return string
   */
  private function clean($str) {

    return strtoupper(str_replace(array(' ', '-', '.', ','), '', trim($str)));
  }


  /**
   * Extract the VAT number from the current VAT-ID
   * 
   * @return string
   */
  private function getVatNumber() {

    return substr($this->vatId['vatId'], 2);
  }


  /**
   * Extract the country code from the current VAT-ID
   * 
   * @return string
   */
  private function getCountryCode() {

    return substr($this->vatId['vatId'], 0, 2);
  }


  /**
   * Sead a SOAP request to validate the current VAT-ID
   * (WSDL description: http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl)
   * 
   * @throws SoapFault
   * @return object
   */
  private function sendValidationRequest() {

    try {
      $response = $this->soapClient->checkVat(array(
        'vatNumber'   => $this->vatId['vatNumber'],
        'countryCode' => $this->vatId['countryCode']
      ));

      return $response;
    }
    catch(SoapFault $e) {
      echo 'Error fetching SOAP results: ' . $e->faultstring;
    }
  }
}
?>