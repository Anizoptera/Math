<?php

namespace Aza\Components\Math;
use Aza\Components\Math\BigMath\BCMath;
use Aza\Components\Math\BigMath\GMPMath;
use Aza\Components\Math\BigMath\PHPMath;

/**
 * Simple math for big integers
 *
 * @project    Anizoptera CMF
 * @package    system.math
 * @subpackage BigMath
 * @author     Amal Samally <amal.samally at gmail.com>
 * @license    MIT
 */
abstract class BigMath
{
	/**
	 * Get an instance of the big math class
	 *
	 * This is NOT a singleton. It simply loads the proper strategy
	 * given the current server configuration
	 *
	 * @return BigMath A big math instance
	 */
	public static function createFromServerConfiguration()
	{
		//@codeCoverageIgnoreStart
		if (extension_loaded('bcmath')) {
			return new BCMath();
		} else if (extension_loaded('gmp')) {
			return new GMPMath();
		}
		return new PHPMath();
		//@codeCoverageIgnoreEnd
	}


	/**
	 * Add two numbers together
	 *
	 * @param string $left  The left argument
	 * @param string $right The right argument
	 *
	 * @return string A base-10 string of the sum of the two arguments
	 */
	abstract public function add($left, $right);

	/**
	 * Subtract two numbers
	 *
	 * @param string $left  The left argument
	 * @param string $right The right argument
	 *
	 * @return string A base-10 string of the difference of the two arguments
	 */
	abstract public function subtract($left, $right);
}
