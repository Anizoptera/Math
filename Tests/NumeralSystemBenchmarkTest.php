<?php

namespace Aza\Components\Math\Tests;
use Aza\Components\Benchmark;
use Aza\Components\Common\Date;
use Aza\Components\Math\NumeralSystem;
use Aza\Components\PhpGen\PhpGen;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionMethod;

/**
 * Number system conversion benchmarks
 *
 * @project Anizoptera CMF
 * @package system.math
 *
 * @group benchmark
 * @coversNothing
 */
class NumeralSystemBenchmarkTest extends TestCase
{
	/**
	 * Common convertion info
	 *
	 * 1. special      - decbin/bindec/decoct/octdec/...
	 * 2. base_convert - base_convert
	 * 3. gmp          - gmp_strval(gmp_init())
	 *
	 * special
	 * ------
	 * - Support of negative integers is platform dependent. Without '-' sign. Not acceptable
	 *   Maximum integer is PHP_INT_MAX (platform dependent)
	 * - Fractions are not supported
	 * + Ignores incorrect symbols
	 * + Fastest variant
	 *
	 * base_convert
	 * ------------
	 * - Negative integers are not supported
	 *   Maximum integer is PHP_INT_MAX+1 (platform dependent)
	 *   Supported bases: between 2 and 36, inclusive
	 * - Fractions are not supported
	 * + Ignores incorrect symbols
	 * - 40-70% slower than "special"
	 *
	 * gmp
	 * ----------------------
	 * + Negative integers are fully supported
	 * + No maximum integer
	 * + Supported bases: from 2 to 62 and -2 to -36
	 * - Fractions are not supported
	 * - Error on incorrect symbols
	 * - 70-215% slower than "special"
	 * - 70-90% slower than "base_convert"
	 */


	/**
	 * Test corner cases in conversion
	 *
	 * @author amal
	 *
	 * @requires extension gmp
	 */
	public function testCornerCases()
	{
		/**
		 * Icorrect symbols
		 *
		 * 1. special      - decbin/bindec/decoct/octdec/...
		 * 2. base_convert - base_convert
		 * 3. gmp          - gmp_strval(gmp_init())
		 *
		 * special
		 * + Ignores incorrect symbols
		 *
		 * base_convert
		 * + Ignores incorrect symbols
		 *
		 * gmp
		 * - Error on incorrect symbols
		 */
		$a = strtoupper('12032e8e7');
		$a = strtoupper('&^%()' . '1y2p0ij32e8e7' . '&^%');

		$b1 = hexdec($a);
		$b2 = base_convert($a, 16, 10);
		$b3 = gmp_strval(gmp_init($a, 16), 10);

		var_dump($a, $b1, $b2, $b3);
	}


	/**
	 * Test GMP number bases alphabet
	 *
	 * @author amal
	 *
	 * @requires extension gmp
	 */
	public function testGmpAlphabet()
	{
		// Supported bases: from 2 to 62 and -2 to -36
		$bases = array_merge(range(-36, -2), range(2, 62));
		$alphabet = array();

		foreach ($bases as $base) {
			$number = abs($base);
			$alphabet[$base] = '';
			for ($i = 0; $i < $number; $i++) {
				$alphabet[$base] .= gmp_strval(gmp_init($i, 10), $base);
			}
		}

		print_r($alphabet);
		/*
			[
				-36 => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
				-35 => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXY',
				-34 => '0123456789ABCDEFGHIJKLMNOPQRSTUVWX',
				-33 => '0123456789ABCDEFGHIJKLMNOPQRSTUVW',
				-32 => '0123456789ABCDEFGHIJKLMNOPQRSTUV',
				-31 => '0123456789ABCDEFGHIJKLMNOPQRSTU',
				-30 => '0123456789ABCDEFGHIJKLMNOPQRST',
				-29 => '0123456789ABCDEFGHIJKLMNOPQRS',
				-28 => '0123456789ABCDEFGHIJKLMNOPQR',
				-27 => '0123456789ABCDEFGHIJKLMNOPQ',
				-26 => '0123456789ABCDEFGHIJKLMNOP',
				-25 => '0123456789ABCDEFGHIJKLMNO',
				-24 => '0123456789ABCDEFGHIJKLMN',
				-23 => '0123456789ABCDEFGHIJKLM',
				-22 => '0123456789ABCDEFGHIJKL',
				-21 => '0123456789ABCDEFGHIJK',
				-20 => '0123456789ABCDEFGHIJ',
				-19 => '0123456789ABCDEFGHI',
				-18 => '0123456789ABCDEFGH',
				-17 => '0123456789ABCDEFG',
				-16 => '0123456789ABCDEF',
				-15 => '0123456789ABCDE',
				-14 => '0123456789ABCD',
				-13 => '0123456789ABC',
				-12 => '0123456789AB',
				-11 => '0123456789A',
				-10 => '0123456789',
				-9  => '012345678',
				-8  => '01234567',
				-7  => '0123456',
				-6  => '012345',
				-5  => '01234',
				-4  => '0123',
				-3  => '012',
				-2  => '01',
				2   => '01',
				3   => '012',
				4   => '0123',
				5   => '01234',
				6   => '012345',
				7   => '0123456',
				8   => '01234567',
				9   => '012345678',
				10  => '0123456789',
				11  => '0123456789a',
				12  => '0123456789ab',
				13  => '0123456789abc',
				14  => '0123456789abcd',
				15  => '0123456789abcde',
				16  => '0123456789abcdef',
				17  => '0123456789abcdefg',
				18  => '0123456789abcdefgh',
				19  => '0123456789abcdefghi',
				20  => '0123456789abcdefghij',
				21  => '0123456789abcdefghijk',
				22  => '0123456789abcdefghijkl',
				23  => '0123456789abcdefghijklm',
				24  => '0123456789abcdefghijklmn',
				25  => '0123456789abcdefghijklmno',
				26  => '0123456789abcdefghijklmnop',
				27  => '0123456789abcdefghijklmnopq',
				28  => '0123456789abcdefghijklmnopqr',
				29  => '0123456789abcdefghijklmnopqrs',
				30  => '0123456789abcdefghijklmnopqrst',
				31  => '0123456789abcdefghijklmnopqrstu',
				32  => '0123456789abcdefghijklmnopqrstuv',
				33  => '0123456789abcdefghijklmnopqrstuvw',
				34  => '0123456789abcdefghijklmnopqrstuvwx',
				35  => '0123456789abcdefghijklmnopqrstuvwxy',
				36  => '0123456789abcdefghijklmnopqrstuvwxyz',
				37  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZa',
				38  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZab',
				39  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabc',
				40  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcd',
				41  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcde',
				42  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdef',
				43  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefg',
				44  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefgh',
				45  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghi',
				46  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghij',
				47  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijk',
				48  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijkl',
				49  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklm',
				50  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmn',
				51  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmno',
				52  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnop',
				53  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopq',
				54  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqr',
				55  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrs',
				56  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrst',
				57  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstu',
				58  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuv',
				59  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvw',
				60  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwx',
				61  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy',
				62  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
			];
		 */
	}

	/**
	 * Test platform length
	 *
	 * @author amal
	 *
	 * @requires extension gmp
	 */
	public function testPlatformIntMaximumLength()
	{
		$bases = array_merge(range(2, 36));
		$systems = array(32 => 2147483647, 64 => 9223372036854775807); //PHP_INT_MAX;
		$data = array();

		foreach ($bases as $base) {
			foreach ($systems as $bits => $number) {
				$number = gmp_strval(gmp_init($number, 10), $base);
				$data[$bits][$base] = strlen($number)-1;
			}
		}

		$data = array(
			array_fill_keys(range(2, 36), 1),
			array_fill_keys(range(2, 62), 1),
		);

		$data = PhpGen::getCode($data, 3, 1);
		print_r($data);
		/*
			[
				32=>[2=>30,3=>19,4=>15,5=>13,6=>11,7=>11,8=>10,9=>9,10=>9,11=>8,12=>8,13=>8,14=>8,15=>7,16=>7,17=>7,18=>7,19=>7,20=>7,21=>7,22=>6,23=>6,24=>6,25=>6,26=>6,27=>6,28=>6,29=>6,30=>6,31=>6,32=>6,33=>6,34=>6,35=>6,36=>5],
				64=>[2=>62,3=>39,4=>31,5=>27,6=>24,7=>22,8=>20,9=>19,10=>18,11=>18,12=>17,13=>17,14=>16,15=>16,16=>15,17=>15,18=>15,19=>14,20=>14,21=>14,22=>14,23=>13,24=>13,25=>13,26=>13,27=>13,28=>13,29=>12,30=>12,31=>12,32=>12,33=>12,34=>12,35=>12,36=>12],
			];

			[
				[2=>1,3=>1,4=>1,5=>1,6=>1,7=>1,8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,18=>1,19=>1,20=>1,21=>1,22=>1,23=>1,24=>1,25=>1,26=>1,27=>1,28=>1,29=>1,30=>1,31=>1,32=>1,33=>1,34=>1,35=>1,36=>1],
				[2=>1,3=>1,4=>1,5=>1,6=>1,7=>1,8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,18=>1,19=>1,20=>1,21=>1,22=>1,23=>1,24=>1,25=>1,26=>1,27=>1,28=>1,29=>1,30=>1,31=>1,32=>1,33=>1,34=>1,35=>1,36=>1,37=>1,38=>1,39=>1,40=>1,41=>1,42=>1,43=>1,44=>1,45=>1,46=>1,47=>1,48=>1,49=>1,50=>1,51=>1,52=>1,53=>1,54=>1,55=>1,56=>1,57=>1,58=>1,59=>1,60=>1,61=>1,62=>1]
			];
		 */
	}


	/**
	 * Test native php functions for converting between
	 * numbersystems for speed and limitations
	 *
	 * @author amal
	 *
	 * @requires extension gmp
	 */
	public function testNativeFuncLimitationsAndSpeed()
	{
		$iteratons = 4000;
		$tests     = 10;
		$results   = array();
		$_data     = array(
			10, 1, 2, 5,
			0,
			2147483646, 2147483647, 2147483648,
			4294967294, 4294967295,
			(int)(PHP_INT_MAX/100000000),
			(int)(PHP_INT_MAX/100000),
			(int)(PHP_INT_MAX/1000),
			(int)(PHP_INT_MAX/10),
			(int)(PHP_INT_MAX/5),
			(int)(PHP_INT_MAX/2),
			PHP_INT_MAX-1000,
			PHP_INT_MAX-1,
			PHP_INT_MAX,
		);


		/**
		 * From base x to y
		 *
		 * 1. base_convert - base_convert($number, $x, $y)
		 * 2. gmp          - gmp_strval(gmp_init($number, $x), $y)
		 */
		if (true) {
			$iteratons = (int)($iteratons/20);
			$tests     = (int)($tests/3);
			$res = array();

			$variants = array(
				array(
					'data' => $_data,
					'from' => 10,
					'to'   => range(2, 36),
				),
				array(
					'data' => array_map('decbin', $_data),
					'from' => 2,
					'to'   => range(2, 36),
				),
				array(
					'data' => array_map('decoct', $_data),
					'from' => 8,
					'to'   => range(2, 36),
				),
				array(
					'data' => array_map('dechex', $_data),
					'from' => 16,
					'to'   => range(2, 36),
				),
			);

			foreach ($variants as $v) {
				$data = $v['data'];
				$from = $v['from'];

				foreach ($v['to'] as $to) {
					foreach ($data as $number) {
						$number = (string)$number;

						$b = (string)base_convert($number, $from, $to);
						$c = (string)gmp_strval(gmp_init($number, $from), $to);
						$this->assertTrue(
							$b === $c,
							"equals: $number ($from => $to)"
						);

						$b = base_convert($b, $to, $from);
						$this->assertTrue(
							$b === $number,
							"base_convert (reverse): $number ($from => $to)"
						);

						$c = gmp_strval(gmp_init($c, $to), $from);
						$this->assertTrue(
							$c === $number,
							"gmp (reverse): $number ($from => $to)"
						);
					}

					for ($j = 0; $j < $tests; $j++) {
						$start = microtime(true);
						for ($i = 0; $i < $iteratons; $i++) {
							foreach ($data as $number) {
								base_convert(
									base_convert($number, $from, $to),
									$to,
									$from
								);
							}
						}
						$res['base_convert'][] = Date::timeEnd($start);

						$start = microtime(true);
						for ($i = 0; $i < $iteratons; $i++) {
							foreach ($data as $number) {
								gmp_strval(gmp_init(
									gmp_strval(gmp_init($number, $from), $to),
									$to), $from
								);
							}
						}
						$res['gmp'][] = Date::timeEnd($start);
					}
				}
			}

			$results['common'] = Benchmark::analyzeResults($res);
		}


		/**
		 * From decimal to binary
		 *
		 * 1. special      - decbin($number)
		 * 2. base_convert - base_convert($number, 10, 2)
		 * 3. gmp          - gmp_strval(gmp_init($number, 10), 2)
		 */
		if (true) {
			$data = $_data;
			foreach ($data as $number) {
				$a = decbin($number);
				$b = base_convert($number, 10, 2);
				$c = gmp_strval(gmp_init($number, 10), 2);
				$this->assertTrue((string)$a === (string)$b, "base_convert: $number");
				$this->assertTrue((string)$b === (string)$c, "gmp: $number");
			}

			$res = array();
			for ($j = 0; $j < $tests; $j++) {
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						decoct($number);
					}
				}
				$res['spec'][] = Date::timeEnd($start);
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						base_convert($number, 10, 8);
					}
				}
				$res['base_convert'][] = Date::timeEnd($start);
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						gmp_strval(gmp_init($number, 10), 8);
					}
				}
				$res['gmp'][] = Date::timeEnd($start);
			}
			$results['decbin'] = Benchmark::analyzeResults($res);
		}

		/**
		 * From binary to decimal
		 *
		 * 1. special      - bindec($number)
		 * 2. base_convert - base_convert($number, 2, 10)
		 * 3. gmp          - gmp_strval(gmp_init($number, 2), 10)
		 */
		if (true) {
			$data = array_map('decbin', $_data);
			foreach ($data as $number) {
				$a = bindec($number);
				$b = base_convert($number, 2, 10);
				$c = gmp_strval(gmp_init($number, 2), 10);
				$this->assertTrue((string)$a === (string)$b, "base_convert: $number");
				$this->assertTrue((string)$b === (string)$c, "gmp: $number");
			}

			$res = array();
			for ($j = 0; $j < $tests; $j++) {
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						bindec($number);
					}
				}
				$res['spec'][] = Date::timeEnd($start);
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						base_convert($number, 2, 10);
					}
				}
				$res['base_convert'][] = Date::timeEnd($start);
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						gmp_strval(gmp_init($number, 2), 10);
					}
				}
				$res['gmp'][] = Date::timeEnd($start);
			}
			$results['bindec'] = Benchmark::analyzeResults($res);
		}


		/**
		 * From decimal to octal
		 *
		 * 1. special      - decoct($number)
		 * 2. base_convert - base_convert($number, 10, 8)
		 * 3. gmp          - gmp_strval(gmp_init($number, 10), 8)
		 */
		if (true) {
			$data = $_data;
			foreach ($data as $number) {
				$a = decoct($number);
				$b = base_convert($number, 10, 8);
				$c = gmp_strval(gmp_init($number, 10), 8);
				$this->assertTrue((string)$a === (string)$b, "base_convert: $number");
				$this->assertTrue((string)$b === (string)$c, "gmp: $number");
			}

			$res = array();
			for ($j = 0; $j < $tests; $j++) {
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						decoct($number);
					}
				}
				$res['spec'][] = Date::timeEnd($start);
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						base_convert($number, 10, 8);
					}
				}
				$res['base_convert'][] = Date::timeEnd($start);
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						gmp_strval(gmp_init($number, 10), 8);
					}
				}
				$res['gmp'][] = Date::timeEnd($start);
			}
			$results['decoct'] = Benchmark::analyzeResults($res);
		}

		/**
		 * From octal to decimal
		 *
		 * 1. special      - octdec($number)
		 * 2. base_convert - base_convert($number, 8, 10)
		 * 3. gmp          - gmp_strval(gmp_init($number, 8), 10)
		 */
		if (true) {
			$data = array_map('decoct', $_data);
			foreach ($data as $number) {
				$a = octdec($number);
				$b = base_convert($number, 8, 10);
				$c = gmp_strval(gmp_init($number, 8), 10);
				$this->assertTrue((string)$a === (string)$b, "base_convert: $number");
				$this->assertTrue((string)$b === (string)$c, "gmp: $number");
			}

			$res = array();
			for ($j = 0; $j < $tests; $j++) {
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						octdec($number);
					}
				}
				$res['spec'][] = Date::timeEnd($start);
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						base_convert($number, 8, 10);
					}
				}
				$res['base_convert'][] = Date::timeEnd($start);
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						gmp_strval(gmp_init($number, 8), 10);
					}
				}
				$res['gmp'][] = Date::timeEnd($start);
			}
			$results['octdec'] = Benchmark::analyzeResults($res);
		}


		/**
		 * From decimal to hexadecimal
		 *
		 * 1. special      - dechex($number)
		 * 2. base_convert - base_convert($number, 10, 16)
		 * 3. gmp          - gmp_strval(gmp_init($number, 10), 16)
		 */
		if (true) {
			$data = $_data;
			foreach ($data as $number) {
				$a = dechex($number);
				$b = base_convert($number, 10, 16);
				$c = gmp_strval(gmp_init($number, 10), 16);
				$this->assertTrue((string)$a === (string)$b, "base_convert: $number");
				$this->assertTrue((string)$b === (string)$c, "gmp: $number");
			}

			$res = array();
			for ($j = 0; $j < $tests; $j++) {
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						dechex($number);
					}
				}
				$res['spec'][] = Date::timeEnd($start);
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						base_convert($number, 10, 16);
					}
				}
				$res['base_convert'][] = Date::timeEnd($start);
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						gmp_strval(gmp_init($number, 10), 16);
					}
				}
				$res['gmp'][] = Date::timeEnd($start);
			}
			$results['dechex'] = Benchmark::analyzeResults($res);
		}

		/**
		 * From hexadecimal to decimal
		 *
		 * 1. special      - hexdec($number)
		 * 2. base_convert - base_convert($number, 16, 10)
		 * 3. gmp          - gmp_strval(gmp_init($number, 16), 10)
		 */
		if (true) {
			$data = array_map('dechex', $_data);
			foreach ($data as $number) {
				$a = hexdec($number);
				$b = base_convert($number, 16, 10);
				$c = gmp_strval(gmp_init($number, 16), 10);
				$this->assertTrue((string)$a === (string)$b, "base_convert: $number");
				$this->assertTrue((string)$b === (string)$c, "gmp: $number");
			}

			$res = array();
			for ($j = 0; $j < $tests; $j++) {
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						hexdec($number);
					}
				}
				$res['spec'][] = Date::timeEnd($start);
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						base_convert($number, 16, 10);
					}
				}
				$res['base_convert'][] = Date::timeEnd($start);
				$start = microtime(true);
				for ($i = 0; $i < $iteratons; $i++) {
					foreach ($data as $number) {
						gmp_strval(gmp_init($number, 16), 10);
					}
				}
				$res['gmp'][] = Date::timeEnd($start);
			}
			$results['hexdec'] = Benchmark::analyzeResults($res);
		}


		print_r($results);
	}


	/**
	 * Test php realization for base converting
	 *
	 * @author amal
	 *
	 * @requires extension gmp
	 */
	public function testOwnRealization()
	{
		/**
		 * Pure PHP realization seems to be
		 * ~6000% slower then GMP
		 *
		 * Optimized conversion with support of native
		 * functions is ~300% slower then GMP
		 */

		$iteratons = 2;
		$tests     = 2;
		$results   = array();

		$_data = array(
			10,
			PHP_INT_MAX,
			bcadd(PHP_INT_MAX, PHP_INT_MAX),
			bcadd(PHP_INT_MAX, bcmul(PHP_INT_MAX, PHP_INT_MAX)),
			bcmul(PHP_INT_MAX, bcmul(PHP_INT_MAX, PHP_INT_MAX)),
			1, 2, 5, 0,
			'2147483646',
			'4294967294',
			(int)(PHP_INT_MAX/22222222),
			(int)(PHP_INT_MAX/2),
			PHP_INT_MAX-1,
		);

		$to = range(2, 62);
		$variants = array(
			array(
				'data' => $_data,
				'from' => 10,
				'to'   => $to,
			),
			array(
				'data' => array_map('decbin', $_data),
				'from' => 2,
				'to'   => $to,
			),
			array(
				'data' => array_map('decoct', $_data),
				'from' => 8,
				'to'   => $to,
			),
			array(
				'data' => array_map('dechex', $_data),
				'from' => 16,
				'to'   => $to,
			),
			array(
				'data' => array_map(function($v) {
					return gmp_strval(gmp_init($v, 10), 35);
				}, $_data),
				'from' => 35,
				'to'   => $to,
			),
		);

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

					$a = gmp_strval(gmp_init($number, $from), $to);
					$b = $NumSysRawConvert->invoke(null, $number, $from, $to);
					$c = NumeralSystem::convert($number, $from, $to);
					$this->assertSame(
						$a, $b,
						"equals1: $number ($from => $to)"
					);
					$this->assertSame(
						$b, $c,
						"equals2: $number ($from => $to)"
					);

					$_a = gmp_strval(gmp_init($a, $to), $from);
					$this->assertSame(
						$_a, $number,
						"gmp (reverse): $number ($from => $to)"
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

				for ($j = 0; $j < $tests; $j++) {
					$start = microtime(true);
					for ($i = 0; $i < $iteratons; $i++) {
						foreach ($data as $number) {
							gmp_strval(gmp_init(
								gmp_strval(gmp_init($number, $from), $to),
								$to),
								$from
							);
						}
					}
					$results['gmp'][] = Date::timeEnd($start);

					$start = microtime(true);
					for ($i = 0; $i < $iteratons; $i++) {
						foreach ($data as $number) {
							NumeralSystem::convert(
								NumeralSystem::convert($number, $from, $to),
								$to,
								$from
							);
						}
					}
					$results['NumeralSystem'][] = Date::timeEnd($start);

					$start = microtime(true);
					for ($i = 0; $i < $iteratons; $i++) {
						foreach ($data as $number) {
							$NumSysRawConvert->invoke(
								null,
								$NumSysRawConvert->invoke(
									null, $number, $from, $to
								),
								$to,
								$from
							);
						}
					}
					$results['NumeralSystem raw'][] = Date::timeEnd($start);
				}
			}
		}

		$results = Benchmark::analyzeResults($results);

		print_r($results);
	}
}
