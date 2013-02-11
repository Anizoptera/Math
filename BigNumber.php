<?php

namespace Aza\Components\Math;
use Aza\Components\Math\Exceptions\Exception;

/**
 * Functionality to
 * Represents a number for arbitrary precision
 * arithmetic computations.
 *
 * Based on {@link https://github.com/moontoast/math Moontoast Math Library}
 *
 * @project Anizoptera CMF
 * @package system.math
 */
class BigNumber
{
	// Round modes
	const ROUND_HALF_UP   = PHP_ROUND_HALF_UP;   // 1
	const ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN; // 2
	/**
	 * Simple cut at the specified digit after dot.
	 * Value choosed to not conflict with PHP_ROUND_* constants.
	 */
	const ROUND_CUT = 8;


	/**
	 * Flag if BCmath extension is available
	 */
	public static $hasBcmath = false;

	/**
	 * Default precision
	 * (number of digits after the decimal place)
	 */
	protected static $defaultScale = -1;


	/**
	 * Number value, as a string
	 *
	 * @var string
	 */
	protected $value;

	/**
	 * The scale for the current number
	 *
	 * @var int
	 */
	protected $scale;



	/**
	 * Constructs an object from a string, integer, float,
	 * or any object that may be cast to a string, resulting
	 * in a numeric string value
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 * @param int $scale [optional] <p>
	 * Specifies the default number of digits after the decimal
	 * place to be used in operations for this number
	 * </p>
	 *
	 * @throws Exception
	 */
	public function __construct($number = 0, $scale = null)
	{
		if (!self::$hasBcmath) {
			throw new Exception(
				"You need BCMath extension enabled to work with BigNumber"
			);
		}

		isset($scale)
			? $this->setCalcScale($scale)
			: $this->scale = self::$defaultScale;

		$this->setValue($number);
	}

	/**
	 * Returns the string value of this number
	 *
	 * @return string String representation of the number
	 */
	public function __toString()
	{
		return $this->getValue();
	}



	/**
	 * Returns the current number scale
	 *
	 * @return int
	 */
	public function getScale()
	{
		$value = $this->trim($this->value);
		return false !== ($pos = strpos($value, '.'))
				? strlen($value) - $pos - 1
				: -1;
	}

	/**
	 * Returns the calculation scale used for this number
	 *
	 * @return int
	 */
	public function getCalcScale()
	{
		return $this->scale;
	}

	/**
	 * Returns the current value of this number (trimmed)
	 *
	 * @return string String representation of the number in base 10
	 */
	public function getValue()
	{
		return $this->trim($this->value);
	}


	/**
	 * Sets the calculation scale of this number
	 *
	 * @param int $scale Specifies the default number of digits after the decimal
	 *                   place to be used in operations for this number
	 *
	 * @return self for chainning
	 */
	public function setCalcScale($scale)
	{
		$this->scale = $scale > 0 ? (int)$scale : 0;
		return $this;
	}

	/**
	 * Sets the value of this number to a new value
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 *
	 * @return self for chainning
	 */
	public function setValue($number)
	{
		// Check for invalid input, empty string (false, null)
		('' === (string)$number)
			&& $number = '0';

		// Set the scale for the number to the scale value passed in
		// bcsub is fastest variant
		$number = bcsub(
			$this->filterNumber($number),
			'0',
			$this->scale
		);

		$this->value = $number;

		return $this;
	}



	/**
	 * Sets the current number to the absolute value of itself
	 *
	 * @return self for chainning
	 */
	public function abs()
	{
		// Negative numbers starts with '-' character
		'-' === $this->value[0]
			&&  $this->value = substr($this->value, 1);

		return $this;
	}


	/**
	 * Adds the given number to the current number
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 *                      representation of a base 10 number
	 *
	 * @return self for chainning
	 * @see bcadd
	 */
	public function add($number)
	{
		$this->value = bcadd(
			$this->value,
			$this->filterNumber($number),
			$this->scale
		);

		return $this;
	}

	/**
	 * Subtracts the given number from the current number
	 *
	 * @see bcsub
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 *
	 * @return self for chainning
	 */
	public function subtract($number)
	{
		$this->value = bcsub(
			$this->value,
			$this->filterNumber($number),
			$this->scale
		);

		return $this;
	}

	/**
	 * Multiplies the current number by the given number
	 *
	 * @see bcmul
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 *
	 * @return self for chainning
	 */
	public function multiply($number)
	{
		$this->value = bcmul(
			$this->value,
			$this->filterNumber($number),
			$this->scale
		);

		return $this;
	}

	/**
	 * Divides the current number by the given number
	 *
	 * @see bcdiv
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 *
	 * @return self for chainning
	 * @throws Exception if $number is zero
	 */
	public function divide($number)
	{
		$number = $this->filterNumber($number);

		if ('0' == $number) {
			throw new Exception('Division by zero');
		}

		$this->value = bcdiv(
			$this->value,
			$number,
			$this->scale
		);

		return $this;
	}


	/**
	 * Finds the modulus (remainder) of the current number
	 * divided by the given number
	 *
	 * @TODO: Support fraction argument for mod()
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 *
	 * @return self for chainning
	 * @throws Exception if $number is zero
	 * @see bcmod
	 */
	public function mod($number)
	{
		$number = $this->filterNumber($number);

		if ('0' === $number) {
			throw new Exception('Division by zero');
		}

		$this->value = bcmod(
			$this->value,
			$number
		);

		return $this;
	}

	/**
	 * Raises current number to the given number
	 *
	 * @TODO: Support fraction argument for pow()
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 *
	 * @return self for chainning
	 * @see bcpow
	 */
	public function pow($number)
	{
		$this->value = bcpow(
			$this->value,
			$this->filterNumber($number),
			$this->scale
		);

		return $this;
	}

	/**
	 * Raises the current number to the $pow, then divides by the $mod
	 * to find the modulus
	 *
	 * This is functionally equivalent code:
	 *
	 * <code>
	 *     (new BigNumber(10))->powMod(20, 30);
	 *     (new BigNumber(10))->pow(20)->mod(30);
	 * </code>
	 *
	 * However powMod() uses bcpowmod(), so it is faster
	 * and can accept larger parameters.
	 *
	 * @TODO: Support fraction arguments for powMod()
	 *
	 * @see bcpowmod
	 *
	 * @param mixed $pow May be of any type that can be cast to a string
	 *                   representation of a base 10 number
	 * @param mixed $mod May be of any type that can be cast to a string
	 *                   representation of a base 10 number
	 *
	 * @return self for chainning
	 *
	 * @throws Exception if $number is zero
	 */
	public function powMod($pow, $mod)
	{
		$mod = $this->filterNumber($mod);

		if ('0' === $mod) {
			throw new Exception('Division by zero');
		}

		// when there's a dot in one of bcpowmod's parameters (it's a float),
		// you'll get a "bc math warning: non-zerio scale in <whatever>"
		// so we need to use trim even for integer value (it will be 12.0000...)
		$val = $this->trim($this->value);
		$pow = $this->filterNumber($pow);
		$this->value = bcpowmod(
			$val, $pow, $mod
		);

		return $this;
	}


	/**
	 * Finds the square root of the current number
	 *
	 * @TODO: Nth root calculation method
	 *
	 * @return self for chainning
	 * @see bcsqrt
	 */
	public function sqrt()
	{
		$this->value = bcsqrt(
			$this->value,
			$this->scale
		);

		return $this;
	}


	/**
	 * Shifts the current number $bits to the left
	 *
	 * @param int $bits
	 *
	 * @return self for chainning
	 */
	public function shiftLeft($bits)
	{
		$this->value = bcmul(
			$this->value,
			bcpow('2', $bits)
		);

		return $this;
	}

	/**
	 * Shifts the current number $bits to the right
	 *
	 * @param int $bits
	 *
	 * @return self for chainning
	 */
	public function shiftRight($bits)
	{
		$this->value = bcdiv(
			$this->value,
			bcpow('2', $bits)
		);

		return $this;
	}


	/**
	 * Returns the rounded value of val to specified precision
	 * (number of digits after the decimal point).
	 *
	 * @param int $precision [optional] <p>
	 * The optional number of decimal digits to round to.
	 * </p>
	 * @param int $mode [optional] <p>
	 * One of BigNumber::ROUND_HALF_UP (equivalent of PHP_ROUND_HALF_UP),
	 * BigNumber::ROUND_HALF_DOWN (equivalent of PHP_ROUND_HALF_DOWN),
	 * BigNumber::ROUND_CUT
	 * </p>
	 *
	 * @return self for chainning
	 */
	public function round($precision = 0, $mode = self::ROUND_HALF_UP)
	{
		if ($precision >= $scale = $this->getScale()) {
			// Value is already rounded
			return $this;
		}

		// ROUND_CUT
		if (self::ROUND_CUT == $mode) {
			$value = bcadd($this->value, 0, $precision);
		}

		// HALF_UP / HALF_DOWN
		else {
			$original = $this->value;
			$floored  = $this->floor()->value;
			$diff     = bcsub($original, $floored, $scale);

			$roundedDiff = round($diff, $precision, $mode);

			$value = bcadd($floored, $roundedDiff, $precision);
		}

		$this->value = $value;

		return $this;
	}

	/**
	 * Finds the next lowest integer value by rounding down
	 * the current number if necessary
	 *
	 * @see floor
	 *
	 * @return self for chainning
	 */
	public function floor()
	{
		$number = $this->value;

		if ($this->isNegative()) {
			// 14 is the magic precision number
			$number = bcadd($number, '0', 14);
			if ('.00000000000000' !== substr($number, -15)) {
				$number = bcsub($number, '1', 0);
			}
		}

		$this->value = bcadd($number, '0', 0);

		return $this;
	}

	/**
	 * Finds the next highest integer value by rounding up
	 * the current number if necessary
	 *
	 * @see ceil
	 *
	 * @return self for chainning
	 */
	public function ceil()
	{
		$number = $this->value;

		if ($this->isPositive()) {
			// 14 is the magic precision number
			$number = bcadd($number, '0', 14);
			if ('.00000000000000' !== substr($number, -15)) {
				$number = bcadd($number, '1', 0);
			}
		}

		$this->value = bcadd($number, '0', 0);

		return $this;
	}


	/**
	 * Increases the value of the current number by one
	 *
	 * @return self for chainning
	 */
	public function increment()
	{
		return $this->add(1);
	}

	/**
	 * Decreases the value of the current number by one
	 *
	 * @return self for chainning
	 */
	public function decrement()
	{
		return $this->subtract(1);
	}


	/**
	 * Compares the current number with the given number
	 *
	 * Returns 0 if the two operands are equal, 1 if the current number is
	 * larger than the given number, -1 otherwise.
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 *
	 * @return int
	 * @see bccomp
	 */
	public function compareTo($number)
	{
		return bccomp(
			$this->value,
			$this->filterNumber($number),
			$this->scale
		);
	}

	/**
	 * Returns true if the current number equals the given number
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 *
	 * @return bool
	 */
	public function isEqualTo($number)
	{
		return 0 == $this->compareTo($number);
	}

	/**
	 * Returns true if the current number is greater than the given number
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 *
	 * @return bool
	 */
	public function isGreaterThan($number)
	{
		return 1 == $this->compareTo($number);
	}

	/**
	 * Returns true if the current number is greater than or equal to the given number
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 *
	 * @return bool
	 */
	public function isGreaterThanOrEqualTo($number)
	{
		return $this->compareTo($number) >= 0;
	}

	/**
	 * Returns true if the current number is less
	 * than the given number
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 *
	 * @return bool
	 */
	public function isLessThan($number)
	{
		return -1 == $this->compareTo($number);
	}

	/**
	 * Returns true if the current number is less
	 * than or equal to the given number
	 *
	 * @param mixed $number <p>
	 * May be of any type that can be cast to a string
	 * representation of a base 10 number.
	 * </p>
	 *
	 * @return bool
	 */
	public function isLessThanOrEqualTo($number)
	{
		return $this->compareTo($number) <= 0;
	}


	/**
	 * Sign function. Returns the sign of the current number
	 *
	 * @return int -1, 0 or 1 as the value of this number is negative, zero or positive
	 */
	public function signum()
	{
		if ($this->isGreaterThan(0)) {
			return 1;
		} else if ($this->isLessThan(0)) {
			return -1;
		}
		return 0;
	}


	/**
	 * Returns true if the current number is a negative number
	 *
	 * @return bool
	 */
	public function isNegative()
	{
		// Negative numbers starts with '-' character
		return '-' === $this->value[0];
	}

	/**
	 * Returns true if the current number is a positive number
	 *
	 * @return bool
	 */
	public function isPositive()
	{
		// Negative numbers starts with '-' character
		return '-' !== $this->value[0];
	}


	/**
	 * Sets the current number to the negative value of itself
	 *
	 * @return self for chainning
	 */
	public function negate()
	{
		// TODO: May be use substr + concatenation? Most likely it'll be faster
		return $this->multiply(-1);
	}


	/**
	 * Returns the current value converted to an arbitrary base
	 *
	 * @see NumeralSystem::convert
	 *
	 * @param int|string $base <p>
	 * Target number system.
	 * </p>
	 *
	 * @return string String representation of the number in the given base
	 */
	public function convertToBase($base)
	{
		return NumeralSystem::convert(
			$this->trim($this->value),
			10, $base
		);
	}



	/**
	 * Filters a number, converting it to a string value.
	 *
	 * Prepares a number without scientific (exponential)
	 * notation and without loosing precision
	 * (as far as possible).
	 *
	 * Without this, floats/doubles loses precision just awfully.
	 * See tests in {@link BigNumberBenchmarkTest::testPrepareFloat}.
	 *
	 * @param mixed $number
	 *
	 * @return string
	 */
	protected function filterNumber($number)
	{
		// 16 is the best value. was obtained empirically
		(16 !== ($oldPrecision =  ini_get('precision')))
			&& ini_set('precision', 16);
		$number = (string)$number;
		(16 !== $oldPrecision)
			&& ini_set('precision', $oldPrecision);

		// Expand scientific (exponential) support
		if (false !== ($pos = strpos($number, 'E'))
		    || false !== ($pos = strpos($number, 'e'))
		) {
			$firstPart = filter_var(
				substr($number, 0, $pos),
				FILTER_SANITIZE_NUMBER_FLOAT,
				FILTER_FLAG_ALLOW_FRACTION
			);
			if ('-' === $number[$pos+1]) {
				$secondPart = filter_var(
					substr($number, $pos+2),
					FILTER_SANITIZE_NUMBER_FLOAT,
					FILTER_FLAG_ALLOW_FRACTION
				);
				return bcdiv(
					$firstPart,
					bcpow('10', $secondPart, 0),
					100
				);
			} else {
				$secondPart = filter_var(
					substr($number, $pos+1),
					FILTER_SANITIZE_NUMBER_FLOAT,
					FILTER_FLAG_ALLOW_FRACTION
				);
				return bcmul(
					$firstPart,
					bcpow('10', $secondPart, 0),
					100
				);
			}
		}

		return filter_var(
			$number,
			FILTER_SANITIZE_NUMBER_FLOAT,
			FILTER_FLAG_ALLOW_FRACTION
		);
	}

	/**
	 * Trims trailing zeros of an arbitrary precision number.
	 *
	 * Uses fastest algorithm. See comparision in
	 * {@link BigNumberBenchmarkTest::testTrim}.
	 *
	 * @param string $number
	 *
	 * @return string
	 */
	protected function trim($number)
	{
		if (false === $pos = strpos($number, '.')) {
			return $number;
		}
		return substr($number, 0, $pos) . rtrim(substr($number, $pos), '.0');
	}



	/**
	 * Changes the default scale
	 *
	 * @return int
	 */
	public static function getDefaultScale()
	{
		return self::$defaultScale;
	}

	/**
	 * Changes the default scale
	 *
	 * @param int $scale
	 */
	public static function setDefaultScale($scale)
	{
		self::$defaultScale = $scale > 0 ? (int)$scale : 0;
	}
}


// @codeCoverageIgnoreStart

// Check if we have BCmath extension enabled
(BigNumber::$hasBcmath = extension_loaded('bcmath'))
	// Check default scale
	&& BigNumber::setDefaultScale(strlen(bcsub(0, 0))-2);

// Set default scale to 100 if not set
(BigNumber::getDefaultScale() < 100)
	&& BigNumber::setDefaultScale(100);

// @codeCoverageIgnoreEnd
