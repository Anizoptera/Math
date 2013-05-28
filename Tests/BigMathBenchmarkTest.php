<?php

namespace Aza\Components\Math\Tests;
use Aza\Components\Benchmark;
use Aza\Components\Common\Date;
use Aza\Components\Math\BigMath\BCMath;
use Aza\Components\Math\BigMath\GMPMath;
use Aza\Components\Math\BigMath\PHPMath;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Simple math for big integers benchmarks
 *
 * @project    Anizoptera CMF
 * @package    system.math
 * @subpackage BigMath
 * @author     Amal Samally <amal.samally at gmail.com>
 * @license    MIT
 *
 * @group benchmark
 * @coversNothing
 *
 * @requires extension bcmath
 * @requires extension gmp
 */
class BigMathBenchmarkTest extends TestCase
{
	/**
	 * Test realizations speed
	 *
	 * @author amal
	 */
	public function testRealizationsSpeed()
	{
		/**
		 * Check current bc scale
		 *
		 * BCMath
		 * + Fastest variant
		 *
		 * GMPMath
		 * - ~50% slower than fastest
		 *
		 * PHPMath
		 * - ~5000% slower than fastest
		 */
		$iteratons = 100;
		$tests     = 5;

		$data = array(
			array('1000', '1000'),
			array('1324546674576580', '1324546674576580'),
			array('13245466745765801324546674576580', '13245466745765801324546674576580'),
			array('1', '1'),
			array('11', '11'),
			array('1111111111', '1111111111'),
			array('555', '555'),
			array('-10', '10'),
			array('10', '-10'),
			array('-10', '-10'),
			array('0', '0'),
			array('5', '0'),
			array('-5', '6'),
			array('0', '6'),
			array('2147483647', '9223372036854775808',),
			array('18446744073709551615', '100000000000'),
			array('200', '250'),
			array('10', '300'),
			array('-1', '-1'),
			array('-5', '5'),
			array('5', '-5'),
			array('5', '6'),
		);

		$gmp    = new GMPMath();
		$bc     = new BCMath();
		$native = new PHPMath();

		$res = array();
		for ($j = 0; $j < $tests; $j++) {
			$start = microtime(true);
			for ($i = 0; $i < $iteratons; $i++) {
				foreach ($data as $d) {
					$bc->add($d[0], $d[1]);
					$bc->subtract($d[0], $d[1]);
					$bc->add($d[1], $d[0]);
					$bc->subtract($d[1], $d[0]);
				}
			}
			$res['BCMath'][] = Date::timeEnd($start);

			$start = microtime(true);
			for ($i = 0; $i < $iteratons; $i++) {
				foreach ($data as $d) {
					$gmp->add($d[0], $d[1]);
					$gmp->subtract($d[0], $d[1]);
					$gmp->add($d[1], $d[0]);
					$gmp->subtract($d[1], $d[0]);
				}
			}
			$res['GMPMath'][] = Date::timeEnd($start);

			$start = microtime(true);
			for ($i = 0; $i < $iteratons; $i++) {
				foreach ($data as $d) {
					$native->add($d[0], $d[1]);
					$native->subtract($d[0], $d[1]);
					$native->add($d[1], $d[0]);
					$native->subtract($d[1], $d[0]);
				}
			}
			$res['PHPMath'][] = Date::timeEnd($start);
		}
		$results = Benchmark::analyzeResults($res);

		print_r($results);
	}
}
