<?php

namespace Aza\Components\Math\BigMath;
use Aza\Components\Math\BigMath;

/**
 * Simple math for big integers using GMP
 *
 * @project    Anizoptera CMF
 * @package    system.math
 * @subpackage BigMath
 * @author     Amal Samally <amal.samally at gmail.com>
 * @license    MIT
 */
class GMPMath extends BigMath
{
	/**
	 * {@inheritdoc}
	 */
	public function add($left, $right)
	{
		return gmp_strval(gmp_add($left, $right));
	}

	/**
	 * {@inheritdoc}
	 */
	public function subtract($left, $right)
	{
		return gmp_strval(gmp_sub($left, $right));
	}
}
