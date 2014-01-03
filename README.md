# European VAT number validation ([VIES]) v.0.2
> [VIES API documentation]


## Getting Started

To start validating VAT-ID's you only need to include in your code: `src/eu_vat_validation.php`. All other files are only needed for PHPUnit and unit tests.

---

## Options / Functions

#### __construct()
- param: `string` $vatId *[optional]*
- throws: `SoapFault`

Tries to establish a connection with the SOAP client. 
If `$vatId` isn't empty, the validation (`$this->validate()`) is triggered right away.

---
#### setVatId()
- param: `string` $vatId
- throws: `Exception`

Sets the current VAT-ID value and extracts the VAT-Number and the country code from it.

---
#### getVatId()
- return: `string`

Gets the last set VAT-ID value.

---
#### getVatIdExtended()
- return: `array`

Gets the last set VAT-ID value and all associated details:
- VAT-ID
- VAT-Number
- Country code
- Is valid?
- Company name *(not always available)*
- Company address *(not always available)*

Example:
```php
Array (
    [vatId] => IT01775560442
    [vatNumber] => 01775560442
    [countryCode] => IT
    [isValid] => 1
    [companyName] => M.A.B. SOFTWARE SRL
    [companyAddress] => C DA CAMPIGLIONE 20 63900 FERMO FM
)
```

---
#### validate()

Sends a request to the [VIES API] and validates the last set VAT-ID value.

---
#### isValid()
- return: `boolean`

Internally triggers `$this->validate()` if it hasn't been executed yet, and returns whether the last set VAT-ID is valid.

---

## Usage Examples

Check if passed VAT-ID is valid:
```php
$vatId = new EuVatValidation('IT01775560442');  
print_r($vatId->isValid());

// Output
true
```
---

Check multiple VAT-ID's:
```php
$vatId = new EuVatValidation;

$vatId->setVatId('IT01775560442');
print_r($vatId->isValid());             // Output: true

$vatId->setVatId('XX123456789');
print_r($vatId->isValid());             // Output: false
```
---

Display all VAT-ID details:
```php
$vatId = new EuVatValidation('IT01775560442');
print_r($vatId->getVatIdExtended());

// Output
Array (
    [vatId] => IT01775560442
    [vatNumber] => 01775560442
    [countryCode] => IT
    [isValid] => 1
    [companyName] => M.A.B. SOFTWARE SRL
    [companyAddress] => C DA CAMPIGLIONE 20 63900 FERMO FM
)
```
---

Using all public class functions:
```php
$vatId = new EuVatValidation;
$vatId->setVatId('IT01775560442');
$vatId->validate();

print_r($vatId->isValid());             // Output: true
print_r($vatId->getVatId());            // Output: 'IT01775560442'

print_r($vatId->getVatIdExtended());

// Output
Array (
    [vatId] => IT01775560442
    [vatNumber] => 01775560442
    [countryCode] => IT
    [isValid] => 1
    [companyName] => M.A.B. SOFTWARE SRL
    [companyAddress] => C DA CAMPIGLIONE 20 63900 FERMO FM
)
```
---


[VIES]:http://ec.europa.eu/taxation_customs/vies/vatRequest.html
[VIES API]:http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl
[VIES API documentation]:http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl






















