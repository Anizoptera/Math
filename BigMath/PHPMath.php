<?php

namespace Aza\Components\Math\BigMath;
use Aza\Components\Math\BigMath;
use Aza\Components\Math\NumeralSystem;

/**
 * Simple math for big integers implemented in PHP
 *
 * @project    Anizoptera CMF
 * @package    system.math
 * @subpackage BigMath
 * @author     Amal Samally <amal.samally at gmail.com>
 * @license    MIT
 */
class PHPMath extends BigMath
{
	/**
	 * {@inheritdoc}
	 */
	public function add($left, $right)
	{
		if (!$left) {
			return $right;
		} else if (!$right) {
			return $left;
		}

		if ('-' === $left[0] && '-' === $right[0]) {
			$negative = '-';
			$left     = substr($left, 1);
			$right    = substr($right, 1);
		} else if ($left[0] === '-') {
			return $this->subtract($right, substr($left, 1));
		} else if ($right[0] === '-') {
			return $this->subtract($left, substr($right, 1));
		} else {
			$negative = '';
		}
		$left  = $this->normalize($left);
		$right = $this->normalize($right);

		$result = NumeralSystem::convert(
			$this->addBinary($left, $right),
			NumeralSystem::BINARY,
			10
		);

		return $negative . $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function subtract($left, $right)
	{
		if (!$right) {
			return $left;
		} else if ('-' === $right[0]) {
			return $this->add($left, substr($right, 1));
		} else if ('-' === $left[0]) {
			return '-' . $this->add(substr($left, 1), $right);
		}

		$left    = $this->normalize($left);
		$right   = $this->normalize($right);
		$results = $this->subtractBinary($left, $right);

		$result = NumeralSystem::convert(
			$results[1],
			NumeralSystem::BINARY,
			10
		);

		return '-0' === ($result = $results[0] . $result)
				? '0'
				: $result;
	}

	/**
	 * Add two binary strings together
	 *
	 * @param string $left  The left argument
	 * @param string $right The right argument
	 *
	 * @return string The binary result
	 */
	protected function addBinary($left, $right)
	{
		$len    = max(strlen($left), strlen($right));
		$left   = str_pad($left, $len, chr(0), STR_PAD_LEFT);
		$right  = str_pad($right, $len, chr(0), STR_PAD_LEFT);
		$result = '';
		$carry  = 0;
		for ($i = 0; $i < $len; $i++) {
			$sum = ord($left[$len - $i - 1])
			       + ord($right[$len - $i - 1])
			       + $carry;
			$result .= chr($sum % 256);
			$carry = $sum >> 8;
		}
		while ($carry) {
			$result .= chr($carry % 256);
			$carry >>= 8;
		}

		return strrev($result);
	}

	/**
	 * Subtract two binary strings using 256's compliment
	 *
	 * @param string $left  The left argument
	 * @param string $right The right argument
	 *
	 * @return string The binary result
	 */
	protected function subtractBinary($left, $right)
	{
		$len    = max(strlen($left), strlen($right));
		$left   = str_pad($left, $len, chr(0), STR_PAD_LEFT);
		$right  = str_pad($right, $len, chr(0), STR_PAD_LEFT);
		$right  = $this->compliment($right);
		$result = $this->addBinary($left, $right);
		if (strlen($result) > $len) {
			// Positive Result
			$carry  = substr($result, 0, -1 * $len);
			$result = substr($result, strlen($carry));

			return array(
				'',
				$this->addBinary($result, $carry)
			);
		}

		return array('-', $this->compliment($result));
	}

	/**
	 * Take the 256 base compliment
	 *
	 * @param string $string The binary string to compliment
	 *
	 * @return string The complimented string
	 */
	protected function compliment($string)
	{
		$result = '';
		$len    = strlen($string);
		for ($i = 0; $i < $len; $i++) {
			$result .= chr(255 - ord($string[$i]));
		}

		return $result;
	}

	/**
	 * Transform a string number into a binary string using base autodetection
	 *
	 * @param string $string The string to transform
	 *
	 * @return string The binary transformed number
	 */
	protected function normalize($string)
	{
		return NumeralSystem::convert(
			$string,
			10,
			NumeralSystem::BINARY
		);
	}
}
