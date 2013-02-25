<?php

namespace Aza\Components\Math\Tests;
use Aza\Components\Math\Exceptions\Exception;
use Aza\Components\Math\NumeralSystem;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionMethod;

/**
 * Testing number system conversion
 *
 * @project Anizoptera CMF
 * @package system.math
 */
class NumeralSystemTest extends TestCase
{
	/**
	 * Tests case insensitivity
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\NumeralSystem
	 */
	public function testCaseInsensitivity()
	{
		$_10to36 = array(
			'1'             => '1',
			'10'            => 'A',
			'100'           => '2S',
			'1000'          => 'RS',
			'10000'         => '7PS',
			'100000'        => '255S',
			'1000000'       => 'LFLS',
			'1000000000'    => 'GJDGXS',
			'1000000000000' => 'CRE66I9S',
		);
		$_36to10 = array(
			'1'         => '1',
			'10'        => '36',
			'100'       => '1296',
			'1000'      => '46656',
			'10000'     => '1679616',
			'100000'    => '60466176',
			'1000000'   => '2176782336',
			'10000000'  => '78364164096',
			'100000000' => '2821109907456',
			'WIKIPEDIA' => '91730738691298',
		);

		$NumSysRawConvert = new ReflectionMethod(
			'Aza\Components\Math\NumeralSystem',
			'rawConvert'
		);
		$NumSysRawConvert->setAccessible(true);

		foreach ($_10to36 as $a => $b) {
			$a = (string)$a;

			$res1 = NumeralSystem::convertTo($a, 36);
			$this->assertSame(strtolower($b), $res1);

			$res2 = NumeralSystem::convertFrom($b, 36);
			$this->assertSame($a, $res2);

			$res3 = $NumSysRawConvert->invoke(null, $b, 36, 10);
			$this->assertSame($a, $res3);
		}

		foreach ($_36to10 as $a => $b) {
			$a = (string)$a;

			$res1 = NumeralSystem::convertFrom($a, 36);
			$this->assertSame($b, $res1);

			$res2 = NumeralSystem::convertTo($b, 36);
			$this->assertSame(strtolower($a), $res2);

			$res3 = $NumSysRawConvert->invoke(null, $a, 36, 10);
			$this->assertSame($b, $res3);

			$res4 = $NumSysRawConvert->invoke(null, $b, 10, 36);
			$this->assertSame(strtolower($a), $res4);
		}
	}


	/**
	 * Test negative numbers conversion
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\NumeralSystem::convert
	 */
	public function testNegativeNumbers()
	{
		$NumSysRawConvert = new ReflectionMethod(
			'Aza\Components\Math\NumeralSystem',
			'rawConvert'
		);
		$NumSysRawConvert->setAccessible(true);

		$hasGmp = NumeralSystem::$hasGmp;

		// ----
		$number = -0;
		$b = NumeralSystem::convert($number, 10, 2);
		$c = decbin($number);
		$this->assertSame('0', $b);
		$this->assertSame($b, $c);
		if ($hasGmp) {
			$a = gmp_strval(gmp_init($number, 10), 2);
			$this->assertSame($b, $a);
		}

		// ----
		$number = '-4294967294';
		$b = NumeralSystem::convert($number, 10, 2);
		$this->assertSame('-11111111111111111111111111111110', $b);
		if ($hasGmp) {
			$a = gmp_strval(gmp_init($number, 10), 2);
			$this->assertSame($b, $a);
		}

		// ----
		$number = -100000;
		$b = NumeralSystem::convert($number, 10, 24);
		$this->assertSame('-75eg', $b);
		if ($hasGmp) {
			$a = gmp_strval(gmp_init($number, 10), 24);
			$this->assertSame($b, $a);
		}

		// ----
		$number = '-9223372036854775807';
		$b = NumeralSystem::convert($number, 10, 2);
		$this->assertSame('-111111111111111111111111111111111111111111111111111111111111111', $b);
		if ($hasGmp) {
			$a = gmp_strval(gmp_init($number, 10), 2);
			$this->assertSame($b, $a);
		}


		// ----
		$number   = '-9223372036854775807';
		$expected = '-1y2p0ij32e8e7';
		$b = NumeralSystem::convert($number, 10, 36);
		$this->assertSame($expected, $b);
		if ($hasGmp) {
			$a = gmp_strval(gmp_init($number, 10), 36);
			$this->assertSame($b, $a);
		}
		$b = NumeralSystem::convert($expected, 36, 10);
		$this->assertSame((string)$number, $b);
		if ($hasGmp) {
			$a = gmp_strval(gmp_init($expected, 36), 10);
			$this->assertSame($b, $a);
		}

		// ----
		$number = -1;
		$b = NumeralSystem::convert($number, 10, 16);
		$this->assertSame('-1', $b);
		if ($hasGmp) {
			$a = gmp_strval(gmp_init($number, 10), 16);
			$this->assertSame($b, $a);
		}


		// NoNegative system
		$alphabet = '.-';
		$name     = 'StrangeSystem';
		NumeralSystem::setSystem($name, $alphabet);

		$exNum = 0;
		try {
			NumeralSystem::convert(-1, 10, $name);
		} catch (Exception $e) {
			$this->assertTrue($e instanceof Exception);
			$exNum++;
		}

		$this->assertSame(
			1, $exNum, "Exception is expected"
		);
	}


	/**
	 * Test custom numeral systems
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\NumeralSystem
	 */
	public function testCustomSystems()
	{
		$alphabet = '!@#$%^&*()_+=-';
		$name     = 'StrangeSystem';
		NumeralSystem::setSystem($name, $alphabet);

		// ----
		$number = '9999';
		$res1 = NumeralSystem::convert($number, 10, $name);
		$this->assertSame('$)!$', $res1);

		// ----
		$res2 = NumeralSystem::convert($res1, $name, 10);
		$this->assertSame($number, $res2);

		// ----
		$number = 'CRE66I9S';
		$res1 = NumeralSystem::convert($number, 36, $name);
		$this->assertSame('$&^(&%%&=%(', $res1);

		// ----
		$res2 = NumeralSystem::convert($res1, $name, 36);
		$this->assertSame(strtolower($number), $res2);


		// Incorrect alphabet
		$exNeed = $exNum = 0;
		try {
			$exNeed++;
			NumeralSystem::setSystem('test', '');
		} catch (Exception $e) {
			$this->assertTrue($e instanceof Exception);
			$exNum++;
		}
		try {
			$exNeed++;
			NumeralSystem::setSystem('test', 'a');
		} catch (Exception $e) {
			$this->assertTrue($e instanceof Exception);
			$exNum++;
		}
		$this->assertSame(
			$exNeed,
			$exNum,
			"{$exNeed} exceptions are expected"
		);
	}


	/**
	 * Test special case - empty string, false, null
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\NumeralSystem::convert
	 */
	public function testEmptyInput()
	{
		// Special case - empty string, false, null
		$converted = NumeralSystem::convert('', 10, 10);
		$this->assertSame('', $converted);
		$converted = NumeralSystem::convert('', 10, 2);
		$this->assertSame('', $converted);
		$converted = NumeralSystem::convert('', 2, 10);
		$this->assertSame('', $converted);
		$converted = NumeralSystem::convert(false, 10, 10);
		$this->assertSame('', $converted);
		$converted = NumeralSystem::convert(null, 10, 2);
		$this->assertSame('', $converted);
	}


	/**
	 * Test GMP check
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\NumeralSystem
	 */
	public function testGmpCheck()
	{
		$hasGmp = NumeralSystem::$hasGmp;

		$this->assertSame(defined('GMP_VERSION'), $hasGmp);
		$this->assertSame(extension_loaded('gmp'), $hasGmp);
		$this->assertSame(function_exists('gmp_init'), $hasGmp);
	}

	/**
	 * Test with no GMP enabled
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\NumeralSystem
	 */
	public function testNoGmp()
	{
		$hasGmp = NumeralSystem::$hasGmp;
		NumeralSystem::$hasGmp = false;

		$converted = NumeralSystem::convert(
			'4294967294429496729442949672944294967294',
			10, 36
		);
		$this->assertSame('5bak5x3lpsf1bfzl9oq9utgflq', $converted);

		$converted = NumeralSystem::convert(
			'2147483646214748364621474836462147483646',
			10, 24
		);
		$this->assertSame('4kb8ehlehl60mc6j37i480elhce56', $converted);

		$number = '784637716923335095224261902710254454442933591094742482943';
		$converted = NumeralSystem::convert($number, 10, 2);
		$this->assertSame(
			'111111111111111111111111111111111111111111111111111111111111101000000000000000000000000000000000000000000000000000000000000010111111111111111111111111111111111111111111111111111111111111111',
			$converted
		);
		$converted = NumeralSystem::convert($number, 10, 62);
		$this->assertSame(
			'LP90nCTXd7OmXvW5MTOQdPCvBNu6eBtX',
			$converted
		);

		$number = '345346635467426247614635735745756748145634567657263463454236246';
		$converted = NumeralSystem::convert($number, 10, 2);
		$this->assertSame(
			'1101011011101000111001001010010101111010011010010010001010101100110110011010001100111110010010001011001000101110010010001100110111111011010011100001111101000011110110000111000101100110110001000101111001010110',
			$converted
		);
		$converted = NumeralSystem::convert($number, 10, 62);
		$this->assertSame(
			'dWv9r7zcqu1xuu3ovqrl9we65sYDzfEhmlC',
			$converted
		);

		$number = '345346635467426247614635735745756748145634567657263463454236246345346635467426247614635735745756748145634567657263463454236246';
		$converted = NumeralSystem::convert($number, 10, 2);
		$this->assertSame(
			'1000001010011010101010100010101001010010100010111010110101011010111001011000010110101000011010101101111000011100100101011000001110100010011011110011011111000111111001010111000010100101001100001101011111111100110100111100001010100111001101100110001100010100011110000001001101111111101111010110001101011110101100111001111111100001100110001001110101101111010001111101000011110110000111000101100110110001000101111001010110',
			$converted
		);
		$converted = NumeralSystem::convert($number, 10, 62);
		$this->assertSame(
			'1Az18U0TfLIl0UTv3sjRj7AKp18UwTfp4TlGPxMcnT7jc9o7HJVYgMyGXxMvnPXuKWRslAk',
			$converted
		);


		// Special test - ignore incorrect chars in own realization
		$number1 = NumeralSystem::convert("1234567890", 10, 16);
		$this->assertSame('499602d2', $number1);
		$number2 = NumeralSystem::convert("1q2w3e4r5t6y7z8~9`0", 10, 16);
		$this->assertSame($number1, $number2);


		NumeralSystem::$hasGmp = $hasGmp;
	}


	/**
	 * Test same system optimization conversion
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\NumeralSystem::convert
	 */
	public function testSameSystemOptimization()
	{

		$test      = 'zxlknvweuvh!@#$%^&*()438fhsvb94f';
		$converted = NumeralSystem::convert($test, 2, 2);
		$this->assertSame($test, $converted);
	}


	/**
	 * Test fractions conversion
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\NumeralSystem::convert
	 */
	public function testFractions()
	{
		// NoFraction system
		$alphabet = '.-';
		$name     = 'StrangeSystem';
		NumeralSystem::setSystem($name, $alphabet);

		$exNum = 0;
		try {
			NumeralSystem::convert(7.02, 10, $name);
		} catch (Exception $e) {
			$this->assertTrue($e instanceof Exception);
			$this->assertContains('Target numeral system', $e->getMessage());
			$exNum++;
		}

		$this->assertSame(
			1, $exNum, "Exception is expected"
		);


		// Dummy - Fractions are currently not supported
		$exNum = 0;
		try {
			NumeralSystem::convert('7.00', 10, 8);
		} catch (Exception $e) {
			$this->assertTrue($e instanceof Exception);
			$exNum++;
		}

		$this->assertSame(
			1, $exNum, "Exception is expected"
		);

		return;

		/** @noinspection PhpUnreachableStatementInspection */

		// Empty fraction part
		$converted = NumeralSystem::convert('7.00', 10, 8);
		$this->assertSame('7', $converted);
		$converted = NumeralSystem::convert('6.00', 10, 7);
		$this->assertSame('6', $converted);
	}


	/**
	 * Simple brutforce testing
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\NumeralSystem::rawConvert
	 */
	public function testUnknownNumericSystem()
	{
		$system = 'unknown';
		$exNeed = $exNum = 0;

		try {
			$exNeed++;
			NumeralSystem::convertTo(12345, $system);
		} catch (Exception $e) {
			$this->assertTrue($e instanceof Exception);
			$exNum++;
		}

		try {
			$exNeed++;
			NumeralSystem::convertFrom(12345, $system);
		} catch (Exception $e) {
			$this->assertTrue($e instanceof Exception);
			$exNum++;
		}

		$this->assertSame(
			$exNeed,
			$exNum,
			"{$exNeed} exceptions are expected"
		);
	}


	/**
	 * Decimal number to different systems conversion provider
	 *
	 * @see testConvert
	 */
	public function providerConvert()
	{
		$data = array(
			array(1000, 10, NumeralSystem::BASE64_URL, 'Po'),
			array(1000, 10, NumeralSystem::BASE64_RFC, 'Po'),
			array(100, 10, NumeralSystem::BASE32_RFC, 'de'),
			array(0, 10, 2, '0'),
			array(-100, 10, 6, '-244'),
			array(-100, 10, 12, '-84'),
			array(-100, 10, 24, '-44'),
			array(-100, 10, 36, '-2s'),
			array(-100, 10, 16, '-64'),
			array(-100, 10, 8, '-144'),
			array(-100, 10, 2, '-1100100'),
			array(-100, 10, 10, '-100'),
			array(100, 10, 6, '244'),
			array(100, 10, 12, '84'),
			array(100, 10, 24, '44'),
			array(100, 10, 36, '2s'),
			array(100, 10, 16, '64'),
			array(100, 10, 8, '144'),
			array(100, 10, 2, '1100100'),
			array(100, 'example', 'example', '100'),
			array(100, 36, 36, '100'),
			array(100, 2, 2, '100'),
			array(100, 10, 10, '100'),
		);

		return $data;
	}

	/**
	 * Decimal number to different systems conversion
	 *
	 * @dataProvider providerConvert
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\NumeralSystem
	 *
	 * @param $number
	 * @param $fromBase
	 * @param $toBase
	 * @param $expected
	 */
	public function testConvert($number, $fromBase, $toBase, $expected)
	{
		$converted = NumeralSystem::convert($number, $fromBase, $toBase);
		$this->assertSame(
			$expected,
			$converted,
			"Failed conversion number '{$number}' "
			."from '{$fromBase}' to '{$toBase}' system"
		);

		$reverse = NumeralSystem::convert($converted, $toBase, $fromBase);
		$this->assertEquals(
			$number,
			$reverse,
			"Failed reverse conversion number '{$number} ($reverse)' "
			."from '{$fromBase}' to '{$toBase}' system"
		);
	}


	/**
	 * Binary data conversion
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\NumeralSystem
	 */
	public function testBinaryAlphabet()
	{
		// Full binary alphabet
		for ($i = 0, $alphabet = ''; $i < 256; $i++) $alphabet .= chr($i);
		$name = 'binary';
		NumeralSystem::setSystem($name, $alphabet);

		// ----
		$var = 'example';
		$expected_hex = sha1($var);
		$expected_bin = sha1($var, true);
		$result_hex   = NumeralSystem::convert($expected_bin, $name, 16);
		$result_bin   = NumeralSystem::convert($expected_hex, 16, $name);
		$result_dec   = NumeralSystem::convertFrom($expected_bin, $name);
		$result_62    = NumeralSystem::convert($expected_bin, $name, 62);

		$this->assertSame(
			'c3499c2729730a7f807efb8676a92dcb6f8a3f8f',
			$expected_hex
		);
		$this->assertSame($expected_hex, $result_hex);
		$this->assertSame($expected_bin, $result_bin);
		$this->assertSame(
			'1114894757552854782121625437503858766054806929295',
			$result_dec
		);
		$this->assertSame(
			'RraoYJ2D9ITz12jWdNdIAvymPhX',
			$result_62
		);


		// ----
		$var = time();
		$expected_hex = ltrim(sha1($var), '0'); // sha1 can return hex value padded with zeros
		$expected_bin = sha1($var, true);
		$result_hex   = NumeralSystem::convert($expected_bin, $name, 16);
		$result_bin   = NumeralSystem::convert($expected_hex, 16, $name);
		$this->assertSame($expected_hex, $result_hex);
		$this->assertSame($expected_bin, $result_bin);
	}


	/**
	 * Consistency brutforce testing
	 *
	 * @author amal
	 * @group unit
	 * @group functional
	 * @covers Aza\Components\Math\NumeralSystem
	 */
	public function testBrutforce($hasGmp = null)
	{
		isset($hasGmp) || $hasGmp = NumeralSystem::$hasGmp;

		$_data = array(
			10,
			PHP_INT_MAX,
			1, 2, 5, 0,
			(int)(PHP_INT_MAX/2),
		);

		$variants = array(
			array(
				'data' => &$_data,
				'from' => 10,
				'to'   => range(2, 62, 3),
			),
		);

		if ($hasGmp) {
			$_data[] = '784637716923335095224261902710254454442933591094742482943';
			$_data[] = '345346635467426247614635735745756748145634567657263463454236246';

			$variants[] = array(
				'data' => array_map(function($v) {
					return gmp_strval(gmp_init($v, 10), 43);
				}, $_data),
				'from' => 43,
				'to'   => range(2, 62, 9),
			);
		} else {
			$variants[] = array(
				'data' => array_map(function($v) {
					return base_convert($v, 10, 35);
				}, $_data),
				'from' => 35,
				'to'   => range(2, 62, 9),
			);
		}

		$NumSysRawConvert = new ReflectionMethod(
			'Aza\Components\Math\NumeralSystem',
			'rawConvert'
		);
		$NumSysRawConvert->setAccessible(true);

		foreach ($variants as $v) {
			$data = $v['data'];
			$from = $v['from'];

			foreach ($v['to'] as $to) {
				foreach ($data as $number) {
					$number = (string)$number;

					$b = $NumSysRawConvert->invoke(null, $number, $from, $to);
					$c = NumeralSystem::convert($number, $from, $to);

					if ($hasGmp) {
						$a = gmp_strval(gmp_init($number, $from), $to);
						$this->assertSame(
							$a, $b,
							"equals1: $number ($from => $to)"
						);

						$_a = gmp_strval(gmp_init($a, $to), $from);
						$this->assertSame(
							$_a, $number,
							"gmp (reverse): $number ($from => $to)"
						);
					}

					$this->assertSame(
						$b, $c,
						"equals2: $number ($from => $to)"
					);


					$_b = $NumSysRawConvert->invoke(null, $b, $to, $from);
					$this->assertSame(
						$_b, $number,
						"ns raw (reverse): $number ($from => $to)"
					);

					$_c = NumeralSystem::convert($c, $to, $from);
					$this->assertSame(
						$_c, $number,
						"ns (reverse): $number ($from => $to)"
					);
				}
			}
		}

		if ($hasGmp) {
			$this->testBrutforce(false);
		}
	}
}
