<?php

namespace Aza\Components\Math\BigMath;
use Aza\Components\Math\BigMath;

/**
 * Simple math for big integers using bcmath
 *
 * @project    Anizoptera CMF
 * @package    system.math
 * @subpackage BigMath
 * @author     Amal Samally <amal.samally at gmail.com>
 * @license    MIT
 */
class BCMath extends BigMath
{
	/**
	 * {@inheritdoc}
	 */
	public function add($left, $right)
	{
		return bcadd($left, $right, 0);
	}

	/**
	 * {@inheritdoc}
	 */
	public function subtract($left, $right)
	{
		return bcsub($left, $right, 0);
	}
}
