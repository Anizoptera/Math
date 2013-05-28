<?php

namespace Aza\Components\Math\Tests\BigMath;
use Aza\Components\Math\BigMath\GMPMath;
use Aza\Components\Math\Tests\BigMathTest;

/**
 * GMPMath testing
 *
 * @project    Anizoptera CMF
 * @package    system.math
 * @subpackage BigMath
 * @author     Amal Samally <amal.samally at gmail.com>
 * @license    MIT
 *
 * @requires extension gmp
 *
 * @covers Aza\Components\Math\BigMath\GMPMath
 */
class GMPMathTest extends BigMathTest
{
	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		self::$instance = new GMPMath;
	}

	/**
	 * {@inheritdoc}
	 */
	public function testInstance()
	{
		$this->assertTrue(self::$instance instanceof GMPMath);
	}
}
