<?php

use Aza\Components\Math\BigNumber;
use Aza\Components\Math\NumeralSystem;

require __DIR__ . '/../vendor/autoload.php';


/**
 * AzaMath examples
 *
 * @project Anizoptera CMF
 * @package system.math
 * @author  Amal Samally <amal.samally at gmail.com>
 * @license MIT
 */



// Example #1 - Numeral systems conversions
// ------------------------

$res = NumeralSystem::convert('WIKIPEDIA', 36, 10);
echo $res . PHP_EOL; // 91730738691298

$res = NumeralSystem::convert('9173073869129891730738691298', 10, 16);
echo $res . PHP_EOL; // 1da3c9f2dd3133d4ed04bce2

$res = NumeralSystem::convertTo('9173073869129891730738691298', 62);
echo $res . PHP_EOL; // BvepB3yk4UBFhGew

$res = NumeralSystem::convertFrom('BvepB3yk4UBFhGew', 62);
echo $res . PHP_EOL; // 9173073869129891730738691298


// Example #2 - Custom numeral system
// ------------------------

// Add new system with custom alphabet
// Each char must appear only once.
// It should use only one byte characters.
$alphabet = '!@#$%^&*()_+=-'; // base 14 equivalent
$system   = 'StrangeSystem';
NumeralSystem::setSystem($system, $alphabet);

$number = '9999';
$res = NumeralSystem::convertTo($number, $system);
echo $res . PHP_EOL; // $)!$

$res = NumeralSystem::convertFrom($res, $system);
echo $res . PHP_EOL; // 9999


// Full binary alphabet
for ($i = 0, $alphabet = ''; $i < 256; $i++) $alphabet .= chr($i);
$system = 'binary';
NumeralSystem::setSystem($system, $alphabet);
// Examples with it
$var = 'example';
$expected_hex = ltrim(sha1($var), '0'); // sha1 can return hex value padded with zeros
$expected_bin = sha1($var, true);       // raw sha1 hash (binary representation)
$result_hex   = NumeralSystem::convert($expected_bin, $system, 16);
$result_bin   = NumeralSystem::convert($expected_hex, 16, $system);
echo $expected_hex . PHP_EOL; // c3499c2729730a7f807efb8676a92dcb6f8a3f8f
echo $result_hex . PHP_EOL;   // c3499c2729730a7f807efb8676a92dcb6f8a3f8f
echo ($expected_bin === $result_bin) . PHP_EOL; // 1



// Example #3 - Arbitrary precision arithmetic
// ------------------------

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

// Comparisions
$number = new BigNumber(10);
echo ($number->compareTo(20) < 0) . PHP_EOL; // 1
echo $number->isLessThan(20) . PHP_EOL; // 1

$number = new BigNumber(20);
echo ($number->compareTo(10) > 0) . PHP_EOL; // 1
echo $number->isGreaterThan(10) . PHP_EOL; // 1

$number = new BigNumber(20);
echo ($number->compareTo(20) === 0) . PHP_EOL; // 1
echo $number->isLessThanOrEqualTo(20) . PHP_EOL; // 1



// Example #4 - Input filtration
// ------------------------

// The arguments of all functions are also filtered.
$number = new BigNumber("9,223 372`036'854,775.808000");
echo $number . PHP_EOL; // 9223372036854775.808



// Example #5 - Do some operations and then convert to base62
// ------------------------

$number = new BigNumber('9223372036854775807');
$number = $number->pow(2)->convertToBase(62);
echo $number . PHP_EOL; // 1wlVYJaWMuw53lV7Cg98qn
