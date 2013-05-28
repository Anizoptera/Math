<?php

namespace Aza\Components\Math\Tests;
use Aza\Components\Math\BigMath;
use Aza\Components\Math\BigMath\BCMath;
use Aza\Components\Math\BigMath\GMPMath;
use Aza\Components\Math\BigMath\PHPMath;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * BigMath testing
 *
 * @project    Anizoptera CMF
 * @package    system.math
 * @subpackage BigMath
 * @author     Amal Samally <amal.samally at gmail.com>
 * @license    MIT
 *
 * @covers Aza\Components\Math\BigMath
 */
class BigMathTest extends TestCase
{
	/**
	 * @var BigMath
	 */
	protected static $instance;


	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		self::$instance = BigMath::createFromServerConfiguration();
	}

	/**
	 * {@inheritdoc}
	 */
	public static function tearDownAfterClass()
	{
		self::$instance = null;
	}



	/**
	 * Provider for integers adding
	 *
	 * @see testAdd
	 *
	 * @return array
	 */
	public function provideAddTest()
	{
		$ret = array(
			array('1', '1', '2'),
			array('11', '11', '22'),
			array('1111111111', '1111111111', '2222222222'),
			array('555', '555', '1110'),
			array('-10', '10', '0'),
			array('10', '-10', '0'),
			array('-10', '-10', '-20'),
			array('0', '0', '0'),
			array('5', '0', '5'),
			array('-5', '6', '1'),
			array('0', '6', '6'),
			array(
				'2147483647',
				'9223372036854775808',
				'9223372039002259455',
			),
			array(
				'18446744073709551615',
				        '100000000000',
				'18446744173709551615',
			),
		);
		return $ret;
	}

	/**
	 * Integers adding test
	 *
	 * @dataProvider provideAddTest
	 * @author amal
	 * @group unit
	 *
	 * @param string $left
	 * @param string $right
	 * @param string $expected
	 */
	public function testAdd($left, $right, $expected)
	{
		$this->assertSame($expected, self::$instance->add($left, $right));
	}


	/**
	 * Provider for integers subtraction
	 *
	 * @return array
	 */
	public function provideSubtractTest()
	{
		return array(
			array('0', '6', '-6'),
			array('1', '1', '0'),
			array('6', '3', '3'),
			array('200', '250', '-50'),
			array('10', '300', '-290'),
			array('-1', '-1', '0'),
			array('-5', '5', '-10'),
			array('5', '-5', '10'),
			array('0', '0', '0'),
			array('5', '0', '5'),
			array('5', '6', '-1'),
			array(
				'2147483647',
				'9223372036854775808',
				'-9223372034707292161',
			),
			array(
				'18446744073709551618',
				       '4000000000000',
				'18446740073709551618',
			),
		);
	}

	/**
	 * Integers subtraction test
	 *
	 * @dataProvider provideSubtractTest
	 * @author amal
	 * @group unit
	 *
	 * @param string $left
	 * @param string $right
	 * @param string $expected
	 */
	public function testSubtract($left, $right, $expected)
	{
		$this->assertSame($expected, self::$instance->subtract($left, $right));
	}


	/**
	 * Instance test
	 *
	 * @author amal
	 * @group unit
	 */
	public function testInstance()
	{
		$instance = self::$instance;

		$this->assertTrue($instance instanceof BigMath);

		if (extension_loaded('bcmath')) {
			$this->assertTrue($instance instanceof BCMath);
		} else if (extension_loaded('gmp')) {
			$this->assertTrue($instance instanceof GMPMath);
		} else {
			$this->assertTrue($instance instanceof PHPMath);
		}
	}
}
