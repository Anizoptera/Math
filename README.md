AzaMath
=======

Anizoptera CMF mathematic component.

https://github.com/Anizoptera/Math

[![Build Status](https://secure.travis-ci.org/Anizoptera/Math.png?branch=master)](http://travis-ci.org/Anizoptera/Math)

Provides functionality to work with large numbers with arbitrary precision (using BCMath).
and universal convertor between numeral systems (supported bases from 2 to 62 inclusive, and custom systems; pure PHP realisation, but can use GMP and core PHP functions for speed optimization).

Features:

* Functionality to work with large numbers (integers, floats) with arbitrary precision (requires [BCMath](http://php.net/bcmath))
* Universal convertor between numeral systems (supported bases from 2 to 62 inclusive, and custom systems; pure PHP realisation, but can use [GMP](http://php.net/gmp) and [core PHP](http://php.net/math) functions for speed optimization). Negative and huge integers are supported.
* Convenient, fully documented and test covered API

AzaMath is a part of Anizoptera CMF, written by [Amal Samally](http://azagroup.ru/contacts#amal) (amal.samally at gmail.com).
Arbitrary precision arithmetic part is based on [Moontoast Math Library](https://github.com/moontoast/math).

Licensed under the MIT License.


Requirements
------------

* PHP 5.3.3 (or later);
* [BCMath (Binary Calculator Arbitrary Precision Mathematics)](http://php.net/bcmath) - Needed to work with arbitrary precision arithmetic operations;
* [GMP (GNU Multiple Precision)](http://php.net/gmp) - Used to speed up number systems conversions and arbitrary precision arithmetic operations;


Installation
------------

The recommended way to install AzaMath is [through composer](http://getcomposer.org).
You can see [package information on Packagist.](https://packagist.org/packages/aza/math)

```JSON
{
    "require": {
        "aza/math": "v1.0"
    }
}
```


Examples
--------

Example #1 - Numeral systems conversions

```php
$res = NumeralSystem::convert('WIKIPEDIA', 36, 10);
echo $res; // 91730738691298

$res = NumeralSystem::convert('9173073869129891730738691298', 10, 16);
echo $res; // 1da3c9f2dd3133d4ed04bce2

$res = NumeralSystem::convert('9173073869129891730738691298', 10, 62);
echo $res; // BvepB3yk4UBFhGew
```

Example #2 - Custom numeral system

```php
// Add new system with custom alphabet
$alphabet = '!@#$%^&*()_+=-';
$name     = 'StrangeSystem';
NumeralSystem::setSystem($name, $alphabet);

$number = '9999';
$res = NumeralSystem::convertTo($number, $name);
echo $res; // $)!$

$res = NumeralSystem::convertFrom($res, $name);
echo $res; // 9999
```

Example #3 - Arbitrary precision arithmetic

```php
// Create new big number with the specified precision for operations (20)
$number = new BigNumber('118059162071741130342591466421', 20);
// Divide number
$number->divide(12345678910);
// See results
echo $number; // 9562792207086578954.49764831288650451382
```

Example #4 - Number filtration

```php
$number = new BigNumber('9,223,372,036,854,775.8080');
echo $number; // 9223372036854775.808
```


Tests
-----

The tests are in the `Tests` folder and reach 100% code-coverage.
To run them, you need PHPUnit.
Example:

    $ phpunit --configuration phpunit.xml.dist


License
-------

MIT, see LICENSE.md
