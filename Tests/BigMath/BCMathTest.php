<?php

namespace Aza\Components\Math\Tests\BigMath;
use Aza\Components\Math\BigMath\BCMath;
use Aza\Components\Math\Tests\BigMathTest;

/**
 * BCMath testing
 *
 * @project    Anizoptera CMF
 * @package    system.math
 * @subpackage BigMath
 * @author     Amal Samally <amal.samally at gmail.com>
 * @license    MIT
 *
 * @requires extension bcmath
 *
 * @covers Aza\Components\Math\BigMath\BCMath
 */
class BCMathTest extends BigMathTest
{
	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		self::$instance = new BCMath;
	}

	/**
	 * {@inheritdoc}
	 */
	public function testInstance()
	{
		$this->assertTrue(self::$instance instanceof BCMath);
	}
}
