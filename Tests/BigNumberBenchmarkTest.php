<?php

namespace Aza\Components\Math\Tests;
use Aza\Components\Benchmark;
use Aza\Components\Common\Date;
use Aza\Components\Math\BigNumber;
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
 *
 * @requires extension bcmath
 */
class BigNumberBenchmarkTest extends TestCase
{
	protected $oldScale;

	protected function setUp()
	{
		$this->oldScale = strlen(bcsub(0, 0))-2;
	}

	protected function tearDown()
	{
		bcscale($this->oldScale);
	}



	/**
	 * Check current bc scale
	 *
	 * @author amal
	 */
	public function testGetCurrentBcscale()
	{
		/**
		 * Check current bc scale
		 *
		 * strlen(bcsub(0, 0))-2
		 * + Fastest variant
		 *
		 * strlen(bcadd(0, 0))-2
		 * - ~1-30% slower than bcsub()
		 *
		 * strlen(bcdiv(0, 1))-2
		 * - ~20-450% slower than bcsub()
		 */
		$iteratons = 3000;
		$tests     = 10;

		bcscale(1000);

		$res = array();
		for ($j = 0; $j < $tests; $j++) {
			$start = microtime(true);
			for ($i = 0; $i < $iteratons; $i++) {
				$v = strlen(bcsub(0, 0))-2;
			}
			$res['bcsub'][] = Date::timeEnd($start);

			$start = microtime(true);
			for ($i = 0; $i < $iteratons; $i++) {
				$v = strlen(bcadd(0, 0))-2;
			}
			$res['bcadd'][] = Date::timeEnd($start);

			$start = microtime(true);
			for ($i = 0; $i < $iteratons; $i++) {
				$v = strlen(bcdiv(0, 1))-2;
			}
			$res['bcdiv'][] = Date::timeEnd($start);
		}
		$results = Benchmark::analyzeResults($res);

		print_r($results);
	}


	/**
	 * Test number trimming from non necessary zeros
	 *
	 * @author amal
	 */
	public function testTrim()
	{
		/**
		 * Check current bc scale
		 *
		 * variant[0] - regexp
		 * - ~20% slower than fastest
		 *
		 * variant[1] - explode
		 * - ~300% slower than fastest
		 *
		 * variant[2] - strpos
		 * + Fastest variant for long fraction part
		 *
		 * variant[3] - strrpos
		 * + Fastest variant for short fraction part
		 */
		$iteratons = 1000;
		$tests     = 10;

		bcscale(1000);

		$variant = array();
		$variant[0] = function($n) {
			$patterns = array('/[\.][0]+$/','/([\.][0-9]*[1-9])([0]*)$/');
			$replaces = array('', '$1');
			return preg_replace($patterns, $replaces, $n);
		};
		$variant[1] = function($n) {
			$n = explode('.', $n, 2);
			if (!isset($n[1])) {
				$n[1] = 0;
			}
			for ($i = (strlen($n[1]) - 1); $i > 0; $i--) {
				if ($n[1][$i] == '0') {
					$n[1] = substr($n[1], 0, -1);
				} else {
					break;
				}
			}
			return (sprintf('%s%s', $n[0], ($n[1] != '0') ? ".{$n[1]}" : ''));
		};
		$variant[2] = function($n) {
			if (false === $pos = strpos($n, '.')) {
				return $n;
			}
			return substr($n, 0, $pos) . rtrim(substr($n, $pos), '.0');
		};
		$variant[3] = function($n) {
			if (false === $pos = strrpos($n, '.')) {
				return $n;
			}
			return substr($n, 0, $pos) . rtrim(substr($n, $pos), '.0');
		};

		$data = array(
			array('1000', '1000'),
			array('1324546674576580', '1324546674576580'),
			array('13245466745765801324546674576580', '13245466745765801324546674576580'),
			array(
				'13245466745765801324546674576580.000000000000000000000010000000000000000000000000000000',
				'13245466745765801324546674576580.00000000000000000000001',
			),
			array(
				'2.00000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
				'2',
			),
			array(
				'2.00000000000000000000000000000000000000000010000000000000000000000000000000000000000000',
				'2.0000000000000000000000000000000000000000001',
			),
			array('1000.0000', '1000'),
			array('1000.1000', '1000.1'),
			array('1000.01000', '1000.01'),
			array('1000.0001', '1000.0001'),
			array('0.0000120000000000000', '0.000012'),
			array('1.2500000000', '1.25'),
			array('100.0000', '100'),
			array('1230.00000000', '1230'),
		);

		foreach ($variant as $v) {
			foreach ($data as $d) {
				list($original, $expected) = $d;
				$result = $v($original);
				$this->assertSame($expected, $result);
			}
		}

		$res = array();
		for ($j = 0; $j < $tests; $j++) {
			$start = microtime(true);
			for ($i = 0; $i < $iteratons; $i++) {
				foreach ($data as $d) {
					$variant[0]($d[0]);
				}
			}
			$res['variant[0]'][] = Date::timeEnd($start);

			$start = microtime(true);
			for ($i = 0; $i < $iteratons; $i++) {
				foreach ($data as $d) {
					$variant[1]($d[0]);
				}
			}
			$res['variant[1]'][] = Date::timeEnd($start);

			$start = microtime(true);
			for ($i = 0; $i < $iteratons; $i++) {
				foreach ($data as $d) {
					$variant[2]($d[0]);
				}
			}
			$res['variant[2]'][] = Date::timeEnd($start);

			$start = microtime(true);
			for ($i = 0; $i < $iteratons; $i++) {
				foreach ($data as $d) {
					$variant[3]($d[0]);
				}
			}
			$res['variant[3]'][] = Date::timeEnd($start);
		}
		$results = Benchmark::analyzeResults($res);

		print_r($results);
	}


	/**
	 * Test floats preparations
	 *
	 * @author amal
	 */
	public function testPrepareFloat()
	{
		// Create array every time
		$data = function($original = false) {
			return $original
					? array(
						"0.000012",
						"0.0000000000000012",
						"0.0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000012",
						"12000000",
						"120000000000000000",
						"120000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000",
						"0.12",
						"12",
						"1.2",
						"123456789123456789",
						"123456789123456789123456789123456789",
						"123456789123456789123456789123456789123456789123456789123456789123456789",
						"123456789123456789.123456789123456789123456789123456789123456789123456789123456789123456789",
						"9999999999999999999999999999999999999999",
					)
					: array(
						12e-6,
						12e-16,
						12e-100,
						12e6,
						12e16,
						12e100,
						0.12,
						12.0,
						1.2,
						123456789123456789,
						123456789123456789123456789123456789,
						123456789123456789123456789123456789123456789123456789123456789123456789,
						123456789123456789.123456789123456789123456789123456789123456789123456789123456789123456789,
						9999999999999999999999999999999999999999,
					);
		};

		// floats preparation variants
		$variant   = array();
		$precision = null;
		$variant[] = function($number) use (&$precision) {
			$append = '';
			$decimals = $precision - floor(log10(abs($number)));
			if (0 > $decimals) {
				/** @noinspection PhpParamsInspection */
				$number  *= pow(10, $decimals);
				$append   = str_repeat('0', -$decimals);
				$decimals = 0;
			}
			$number = number_format($number, $decimals, '.', '').$append;
			return bcsub($number, 0, 100);
		};
		$variant[] = function($number) {
			return bcsub((string)$number, 0, 100);
		};
		$variant[] = function($number) {
			return bcsub($number, 0, 100);
		};

		$bn = new BigNumber();
		$ref = new ReflectionMethod($bn, 'trim');
		$ref->setAccessible(true);


		/**
		 * Check precision option for algorithm
		 *
		 * The best results in declining order:
		 * 16, 15, 14, 13, 12
		 */
		$original = $data(true);
		$fun      = $variant[0];
		$results  = array();
		for ($i = 0; $i < 100; $i++) {
			$precision = $i;
			$res = array_map(function($number) use ($ref, $bn, $fun, $i) {
				return $ref->invoke($bn, $fun($number));
			}, $data());
			foreach ($res as $k => $v) {
				$results[$i][$k] = levenshtein($original[$k], $v);
			}
		}
		unset($i, $k, $v, $fun, $res);
		$results = array_map('array_sum', $results);
		asort($results, SORT_REGULAR);

		print_r($results);
		$precision = 16; // Set to best


		/**
		 * Check best PHP precision option
		 *
		 * The best results in declining order:
		 * 18, 12, 11, 10, 13
		 */
		$results  = array();
		for ($i = 0; $i < 100; $i++) {
			ini_set('precision', $i);
			foreach ($variant as $key => $fun) {
				// Skip key "0" - it's independent of the PHP precision option
				if (!$key) continue;
				$res = array_map(function($number) use ($ref, $bn, $fun, $i) {
					return $ref->invoke($bn, $fun($number));
				}, $data());
				foreach ($res as $k => $v) {
					$results[$i][$k] += levenshtein($original[$k], $v);
				}
			}
		}
		unset($i, $k, $v, $fun, $res, $key, $fun);
		$results = array_map('array_sum', $results);
		asort($results, SORT_REGULAR);

		print_r($results);
		ini_set('precision', 18); // Set to best


		/**
		 * Check floats preparation variants
		 *
		 * The only sane option - $variant[0]
		 * Without this, floats/doubles loses precision just awfully.
		 */
		$results = $r = array();
		foreach ($variant as $key => $fun) {
			$results[$key] = array_map(function($number) use ($ref, $bn, $fun) {
				return $ref->invoke($bn, $fun($number));
			}, $data());

		}
		unset($key, $fun);

		$r[] = $results[0] === $results[1];
		$r[] = $results[0] === $results[2];
		$r[] = $results[1] === $results[2];

		print_r($results);
	}
}
