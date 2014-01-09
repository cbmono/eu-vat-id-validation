<?php
require_once dirname(__FILE__) . '/../src/eu_vat_validation.php';


class EuVatValidationWrapper extends EuVatValidation {

  public function __construct($vatId = null) {

    if (!empty($vatId)) {
      parent::__construct($vatId);
    }
  }
}


class ViesSoapClient {
  
  public function checkVat() {

    return 'Nothing to return';
  }
}