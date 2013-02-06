<?php

namespace Aza\Components\Math\Tests;
use Aza\Components\Benchmark;
use Aza\Components\Common\Date;
use Aza\Components\Math\NumeralSystem;
use Aza\Components\PhpGen\PhpGen;
use PHPUnit_Framework_TestCase as TestCase;

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
}
