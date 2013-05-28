<?php

namespace Aza\Components\Math\Tests\BigMath;
use Aza\Components\Math\BigMath\PHPMath;
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
 * @covers Aza\Components\Math\BigMath\PHPMath
 */
class PHPMathTest extends BigMathTest
{
	/**
	 * {@inheritdoc}
	 */
	public static function setUpBeforeClass()
	{
		self::$instance = new PHPMath;
	}

	/**
	 * {@inheritdoc}
	 */
	public function testInstance()
	{
		$this->assertTrue(self::$instance instanceof PHPMath);
	}
}
