<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../src/eu_vat_validation.php';


/**
 *  Run tests: 
 *  php vendor/phpunit/phpunit/phpunit.php --colors tests/eu_vat_validation_test.php
 */
class EuVatValidationTest extends PHPUnit_Framework_TestCase {

  protected $vatId;
  protected $right_vatNumber = 'DE273616207';
  protected $wrong_vatNumber = 'DE273616207xxx';


  protected function setUp() {

    $this->vatId = new EuVatValidation();
    $this->mockSoapClient();
  }


  protected function tearDown() {

    $this->vatId = null;
  }


  /**
   * Mock the SOAP client to reduce hits on VIES website
   * 
   * @param boolean $success
   */
  private function mockSoapClient($success = true) {

    $this->vatId->soapClient = $this->getMockFromWsdl(
      'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl', 
      'checkVatService'
    );

    $results = 'soapClientResults' . ($success ? 'Success' : 'Fail');
    $this->vatId->soapClient->expects($this->any())
                            ->method('checkVat')
                            ->will($this->returnValue($this->$results()));
  }


  /**
   * Successful mocked SOAP results
   * 
   * @return object
   */
  private function soapClientResultsSuccess() {

    $result = new StdClass;
    $result->countryCode = 'DE';
    $result->vatNumber = '273616207';
    $result->requestDate = '2013-01-01 00:00:00';
    $result->valid = true;
    $result->name = 'Ondango';
    $result->address = '1600 Amphitheatre Pkwy, Mountain View, CA';

    return $result;
  }


  /**
   * Failed mocked SOAP results
   * 
   * @return object
   */
  private function soapClientResultsFail() {

    $result = new StdClass;
    $result->countryCode = 'xx';
    $result->vatNumber = 'xxxxxxxxxx';
    $result->requestDate = '3099-01-01 00:00:00';
    $result->valid = false;
    $result->name = null;
    $result->address = null;

    return $result;
  }



  /***************************************************************
   * 
   * UNIT TEST's
   * 
   ***************************************************************/

  /**
   * @covers EuVatValidation
   */
  public function testClassExistance() {

    $this->assertInstanceOf('EuVatValidation', new EuVatValidation);
  }


  /**
   * @covers EuVatValidation::__construct
   */
  public function testConstructorWithArg() {

    $vatId = new EuVatValidation($this->right_vatNumber);
    $this->assertEquals($this->right_vatNumber, $vatId->getVatId());
  }


  /**
   * @covers EuVatValidation::setVatId
   * @covers EuVatValidation::getVatId
   * @covers EuVatValidation::getVatIdExtended
   */
  public function testSetterAndGetters() {

    $dirty_vatNumber = ' de-27,3616.207';

    $this->vatId->setVatId($dirty_vatNumber);
    $details = $this->vatId->getVatIdExtended();

    $this->assertEquals($this->right_vatNumber, $this->vatId->getVatId());
    $this->assertEquals($this->right_vatNumber, $details['vatId']);
    $this->assertEquals('273616207', $details['vatNumber']);
    $this->assertEquals('DE', $details['countryCode']);
    $this->assertNull($details['isValid']);
  }


  /**
   * @covers EuVatValidation::setVatId
   * @expectedException Exception
   */
  public function testEmptyVatId() {

    $this->vatId->setVatId('');
  }


  /**
   * @covers EuVatValidation::validate
   */
  public function testValidate() {

    // Success
    $this->mockSoapClient();
    $this->vatId->setVatId($this->right_vatNumber);
    $this->vatId->validate();
    $details = $this->vatId->getVatIdExtended();

    $this->assertEquals('273616207', $details['vatNumber']);
    $this->assertEquals('DE', $details['countryCode']);
    $this->assertEquals('Ondango', $details['companyName']);
    $this->assertEquals('1600 Amphitheatre Pkwy, Mountain View, CA', $details['companyAddress']);
    $this->assertTrue($details['isValid']);

    // Fail
    $this->mockSoapClient(false);
    $this->vatId->setVatId($this->wrong_vatNumber);
    $this->vatId->validate();
    $details = $this->vatId->getVatIdExtended();

    $this->assertNull($details['companyName']);
    $this->assertNull($details['companyAddress']);
    $this->assertFalse($details['isValid']);
  }


  /**
   * @covers EuVatValidation::isValid
   */
  public function testIsValid() {

    // Success
    $this->mockSoapClient();
    $this->vatId->setVatId($this->right_vatNumber);
    $this->assertTrue($this->vatId->isValid());

    // Fail
    $this->mockSoapClient(false);
    $this->vatId->setVatId($this->wrong_vatNumber);
    $this->assertFalse($this->vatId->isValid());
  }
}