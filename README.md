AzaMath
=======

Anizoptera CMF mathematic component.

https://github.com/Anizoptera/Math

[![Build Status](https://secure.travis-ci.org/Anizoptera/Math.png?branch=master)](http://travis-ci.org/Anizoptera/Math)

Provides functionality to work with large numbers with arbitrary precision (using BCMath).
and universal convertor between numeral systems (supported bases from 2 to 62 inclusive, and custom systems; pure PHP realisation, but can use GMP and core PHP functions for speed optimization).

Features:

* Functionality to work with large numbers (integers, floats) with arbitrary precision (requires [BCMath](http://php.net/bcmath)). Can work with floats (E notation too!) and without loosing precision (as far as possible).
* Universal number (and huge number!) convertor between numeral systems (supported bases from 2 to 62 inclusive, and custom systems; pure PHP realisation, but can use [GMP](http://php.net/gmp) and [core PHP](http://php.net/math) functions for speed optimization). Negative and huge integers are supported.
* Convenient, fully documented and test covered API

AzaMath is a part of Anizoptera CMF, written by [Amal Samally](https://github.com/amal) (amal.samally at gmail.com).
Arbitrary precision arithmetic part is partially based on [Moontoast Math Library](https://github.com/moontoast/math).

Licensed under the MIT License.


Requirements
------------

* PHP 5.3.3 (or later);
* [BCMath (Binary Calculator Arbitrary Precision Mathematics)](http://php.net/bcmath) - Required to work with arbitrary precision arithmetic operations;
* [GMP (GNU Multiple Precision)](http://php.net/gmp) - Used to speed up number systems conversions and (in future) arbitrary precision arithmetic operations;


Installation
------------

The recommended way to install AzaMath is [through composer](http://getcomposer.org).
You can see [package information on Packagist.](https://packagist.org/packages/aza/math)

```JSON
{
    "require": {
        "aza/math": "~1.0"
    }
}
```


Examples
--------

Example #1 - Numeral systems conversions

```php
$res = NumeralSystem::convert('WIKIPEDIA', 36, 10);
echo $res . PHP_EOL; // 91730738691298

$res = NumeralSystem::convert('9173073869129891730738691298', 10, 16);
echo $res . PHP_EOL; // 1da3c9f2dd3133d4ed04bce2

$res = NumeralSystem::convert('9173073869129891730738691298', 10, 62);
echo $res . PHP_EOL; // BvepB3yk4UBFhGew
```

Example #2 - Custom numeral system

```php
// Add new system with custom alphabet
$alphabet = '!@#$%^&*()_+=-'; // base 14 equivalent
$name     = 'StrangeSystem';
NumeralSystem::setSystem($name, $alphabet);

$number = '9999';
$res = NumeralSystem::convertTo($number, $name);
echo $res . PHP_EOL; // $)!$

$res = NumeralSystem::convertFrom($res, $name);
echo $res . PHP_EOL; // 9999
```

Example #3 - Arbitrary precision arithmetic

```php
// Create new big number with the specified precision for operations - 20 (default is 100)
$number = new BigNumber('118059162071741130342591466421', 20);

// Divide number
$number->divide(12345678910);
echo $number . PHP_EOL; // 9562792207086578954.49764831288650451382

// Divide again and round with the specified precision and algorithm
// Three round algorithms a supported: HALF_UP, HALF_DOWN, CUT.
// You can use them as BigNumber::ROUND_* or PHP_ROUND_HALF_UP, PHP_ROUND_HALF_DOWN.
// Default is HALF_UP.
$number->divide(9876543210)->round(3, PHP_ROUND_HALF_DOWN);
echo $number . PHP_EOL; // 968232710.955
```

Example #4 - Input filtration

```php
$number = new BigNumber("9,223 372`036'854,775.808000");
echo $number . PHP_EOL; // 9223372036854775.808
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


Links
-----

[(RU) AzaMath — Конвертация систем счисления (включая кастомные) + арифметика произвольной точности](http://habrahabr.ru/post/168935/)
