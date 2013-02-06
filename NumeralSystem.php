<?php

namespace Aza\Components\Math;
use Aza\Components\Common\CArray;
use Aza\Components\Math\Exceptions\Exception;

/**
 * Universal convertor between numeral systems.
 *
 * Supported bases from 2 to 62 inclusive, and custom numeral systems
 * (see {@link NumeralSystem::setSystem setSystem} method).
 *
 * Pure PHP realisation, but can use {@link http://php.net/gmp GMP}
 * and {@link http://php.net/math core PHP} functions for speed improvement.
 *
 * @link http://en.wikipedia.org/wiki/Numeral_system
 *
 * @uses extension-gmp
 * @uses BigNumber
 *
 * @project Anizoptera CMF
 * @package system.math
 */
abstract class NumeralSystem
{
	// Bundled custom numeral systems

	/**
	 * RFC 4648 compliant Base32 alphabit (not original base32 encoding!)
	 * Base32 representation takes roughly 20% more space than Base64.
	 */
	const BASE32_RFC = '32rfc';

	/**
	 * Standard 'Base64' alphabit for RFC 4648 (not original base64 encoding!)
	 *
	 * @link http://tools.ietf.org/html/rfc4648
	 */
	const BASE64_RFC = '64rfc';

	/**
	 * Base64 with URL and Filename Safe Alphabet
	 * (RFC 4648 'base64url', not original base64 encoding!)
	 *
	 * @link http://tools.ietf.org/html/rfc4648
	 */
	const BASE64_URL = '64url';



	/**
	 * Flag if GMP extension is available
	 */
	public static $hasGmp = false;

	/**
	 * Numeral systems settings
	 */
	protected static $systems = array(
		/*
		 * Alphabet for bases between 2 and 36, inclusive
		 */
		'_36' => array(
			'0123456789abcdefghijklmnopqrstuvwxyz',
			array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,'a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35),
		),

		/*
		 * Alphabet for bases between 37 and 62, inclusive
		 * We use different alphabets for bases over an lower than 36 as GMP does
		 */
		'_62' => array(
			'0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
			array(0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,'A'=>10,'B'=>11,'C'=>12,'D'=>13,'E'=>14,'F'=>15,'G'=>16,'H'=>17,'I'=>18,'J'=>19,'K'=>20,'L'=>21,'M'=>22,'N'=>23,'O'=>24,'P'=>25,'Q'=>26,'R'=>27,'S'=>28,'T'=>29,'U'=>30,'V'=>31,'W'=>32,'X'=>33,'Y'=>34,'Z'=>35,'a'=>36,'b'=>37,'c'=>38,'d'=>39,'e'=>40,'f'=>41,'g'=>42,'h'=>43,'i'=>44,'j'=>45,'k'=>46,'l'=>47,'m'=>48,'n'=>49,'o'=>50,'p'=>51,'q'=>52,'r'=>53,'s'=>54,'t'=>55,'u'=>56,'v'=>57,'w'=>58,'x'=>59,'y'=>60,'z'=>61),
		),

		self::BASE32_RFC => array(
			'abcdefghijklmnopqrstuvwxyz234567',
			array('a'=>0,'b'=>1,'c'=>2,'d'=>3,'e'=>4,'f'=>5,'g'=>6,'h'=>7,'i'=>8,'j'=>9,'k'=>10,'l'=>11,'m'=>12,'n'=>13,'o'=>14,'p'=>15,'q'=>16,'r'=>17,'s'=>18,'t'=>19,'u'=>20,'v'=>21,'w'=>22,'x'=>23,'y'=>24,'z'=>25,2=>26,3=>27,4=>28,5=>29,6=>30,7=>31),
		),

		self::BASE64_RFC => array(
			'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/',
			array('A'=>0,'B'=>1,'C'=>2,'D'=>3,'E'=>4,'F'=>5,'G'=>6,'H'=>7,'I'=>8,'J'=>9,'K'=>10,'L'=>11,'M'=>12,'N'=>13,'O'=>14,'P'=>15,'Q'=>16,'R'=>17,'S'=>18,'T'=>19,'U'=>20,'V'=>21,'W'=>22,'X'=>23,'Y'=>24,'Z'=>25,'a'=>26,'b'=>27,'c'=>28,'d'=>29,'e'=>30,'f'=>31,'g'=>32,'h'=>33,'i'=>34,'j'=>35,'k'=>36,'l'=>37,'m'=>38,'n'=>39,'o'=>40,'p'=>41,'q'=>42,'r'=>43,'s'=>44,'t'=>45,'u'=>46,'v'=>47,'w'=>48,'x'=>49,'y'=>50,'z'=>51,0=>52,1=>53,2=>54,3=>55,4=>56,5=>57,6=>58,7=>59,8=>60,9=>61,'+'=>62,'/'=>63),
		),

		self::BASE64_URL => array(
			'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_',
			array('A'=>0,'B'=>1,'C'=>2,'D'=>3,'E'=>4,'F'=>5,'G'=>6,'H'=>7,'I'=>8,'J'=>9,'K'=>10,'L'=>11,'M'=>12,'N'=>13,'O'=>14,'P'=>15,'Q'=>16,'R'=>17,'S'=>18,'T'=>19,'U'=>20,'V'=>21,'W'=>22,'X'=>23,'Y'=>24,'Z'=>25,'a'=>26,'b'=>27,'c'=>28,'d'=>29,'e'=>30,'f'=>31,'g'=>32,'h'=>33,'i'=>34,'j'=>35,'k'=>36,'l'=>37,'m'=>38,'n'=>39,'o'=>40,'p'=>41,'q'=>42,'r'=>43,'s'=>44,'t'=>45,'u'=>46,'v'=>47,'w'=>48,'x'=>49,'y'=>50,'z'=>51,0=>52,1=>53,2=>54,3=>55,4=>56,5=>57,6=>58,7=>59,8=>60,9=>61,'-'=>62,'_'=>63),
			'noNegative' => true,
		),
	);


	/**
	 * Maximum allowed numbers length for
	 * supported positional numeric systems
	 *
	 * @var array
	 * @access protected
	 */
	public static $maxLengthForSpeedup;

	/**
	 * Supported positional numeric systems
	 */
	protected static $posNumSystems = array(
		2=>1,3=>1,4=>1,5=>1,6=>1,7=>1,8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,18=>1,19=>1,20=>1,21=>1,22=>1,23=>1,24=>1,25=>1,26=>1,27=>1,28=>1,29=>1,30=>1,31=>1,32=>1,33=>1,34=>1,35=>1,36=>1
	);

	/**
	 * Supported positional numeric systems for GMP conversion
	 */
	protected static $posNumSystemsGmp = array(
		2=>1,3=>1,4=>1,5=>1,6=>1,7=>1,8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,18=>1,19=>1,20=>1,21=>1,22=>1,23=>1,24=>1,25=>1,26=>1,27=>1,28=>1,29=>1,30=>1,31=>1,32=>1,33=>1,34=>1,35=>1,36=>1,37=>1,38=>1,39=>1,40=>1,41=>1,42=>1,43=>1,44=>1,45=>1,46=>1,47=>1,48=>1,49=>1,50=>1,51=>1,52=>1,53=>1,54=>1,55=>1,56=>1,57=>1,58=>1,59=>1,60=>1,61=>1,62=>1
	);



	/**
	 * Returns a string containing number represented in base tobase.
	 *
	 * @see convert
	 * @see convertFrom
	 *
	 * @param string|int|float $number <p>
	 * The decimal number to convert.
	 * </p>
	 * @param int|string $toBase <p>
	 * Target number system.
	 * </p>
	 *
	 * @return string Result number in the specified system
	 */
	public static function convertTo($number, $toBase)
	{
		return self::convert($number, 10, $toBase);
	}

	/**
	 * Returns a string containing number represented in decimal base.
	 *
	 * @see convert
	 * @see convertTo
	 *
	 * @param string|int|float $number <p>
	 * The decimal number to convert.
	 * </p>
	 * @param int|string $fromBase <p>
	 * Original number numeral system.
	 * </p>
	 *
	 * @return string Result number in the decimal system
	 */
	public static function convertFrom($number, $fromBase)
	{
		return self::convert($number, $fromBase, 10);
	}

	/**
	 * Returns a string containing number represented in base tobase.
	 * The base in which number is given is specified in frombase.
	 *
	 * Basically both frombase and tobase have to be between 2 and 62, inclusive.
	 *
	 * Additonally supported named numerical systems. And you can use
	 * {@link setSystem} to add your custom system.
	 *
	 * Negative numbers and fractions are supported (but only if numeric system
	 * does not contain chars '-' and '.' respectively).
	 *
	 * @param string|int|float $number <p>
	 * The number to convert.
	 * </p>
	 * @param int|string $fromBase <p>
	 * Original number numeral system.
	 * </p>
	 * @param int|string $toBase <p>
	 * Target number system.
	 * </p>
	 *
	 * @return string Result number in the specified system
	 *
	 * @throws Exception
	 */
	public static function convert($number, $fromBase, $toBase)
	{
		$number = (string)$number;

		// OPTIMIZATION: To and from base are same.
		// + Special case - invalid input, empty string (false, null)
		if ($fromBase === $toBase || '' === $number) {
			return $number;
		}

		// OPTIMIZATION: '0 === 0' In all supported positional numeric systems
		else if (($positionalBase = isset(
		            self::$posNumSystemsGmp[$fromBase],
		            self::$posNumSystemsGmp[$toBase])
		         )
		         && '0' === $number
		         // Special case - negative zero handled differently in
		         //   different implementations. So processed here.
		         || '-0' === $number
		) {
			return '0';
		}

		// Negative numbers starts with '-' character
		if (($isNegative = '-' === $number[0])
		    && empty(self::$systems[$fromBase]['noNegative'])
		) {
			if (!empty(self::$systems[$toBase]['noNegative'])) {
				throw new Exception(
					"Target numeral system [$toBase] does not support negative numbers"
				);
			}
			$prefix = '-';
			$number = substr($number, 1);
		} else {
			$prefix = '';
		}

		// Fractions (floats) are not supported in other implementations
		// so convert here
		if (false !== $fractionPos = strpos($number, '.')
		    && empty(self::$systems[$fromBase]['noFraction'])
		) {
			if (!empty(self::$systems[$toBase]['noFraction'])) {
				throw new Exception(
					"Target numeral system [$toBase] does not support fractions"
				);
			}

			/**
			 * @TODO: Fractions support
			 * @link http://www.knowledgedoor.com/2/calculators/convert_a_number_with_a_non-repeating_fractional_part.html
			 * @link http://en.wikipedia.org/wiki/Hexatrigesimal
			 * @link http://en.wikipedia.org/wiki/Base_24#Fractions
			 * @link http://www.exploringbinary.com/converting-a-bicimal-to-a-fraction-direct-method/
			 *
			 * @TODO Recurring fractions support
			 * @link http://en.wikipedia.org/wiki/Repeating_decimal
			 * @link http://www.mymathforum.com/viewtopic.php?f=8&t=25386
			 */
			throw new Exception(
				"Fractions conversion is not supported"
			);

			/** @noinspection PhpUnreachableStatementInspection */

			// Convert the integer part
			$part1 = substr($number, 0, $fractionPos);
			$part1 = self::convert($part1, $fromBase, $toBase);

			// Convert fractional part
			$part2 = substr($number, $fractionPos + 1);
			if ('10' !== (string)$fromBase) {
				// We need number in decimal to work with it
				$part2 = self::convert($part2, $fromBase, 10);
			}
			// Trim zeros
			if ('' === $part2 = rtrim($part2, '0')) {
				return "{$prefix}{$part1}";
			}
			// Get alphabets (from, to) lengths
			if ($positionalBase) {
				$abcFromSize = $fromBase;
				$abcToSize   = $toBase;
			} else {
				if (isset(self::$posNumSystems[$fromBase])) {
					$abcFromSize = $fromBase;
				} else {
					if (!isset(self::$systems[$fromBase][0])) {
						throw new Exception(
							"Unknown source number system [$fromBase]"
						);
					}
					$abcFromSize = strlen(self::$systems[$fromBase][0]);
				}
				if (isset(self::$posNumSystems[$toBase])) {
					$abcToSize = $toBase;
				} else {
					if (!isset(self::$systems[$toBase][0])) {
						throw new Exception(
							"Unknown target number system [$toBase]"
						);
					}
					$abcToSize = strlen(self::$systems[$fromBase][0]);
				}
			}
			// Get result in decimal
			// Divide target radix by the denominator of the decimal fraction
			$denominator = new BigNumber($abcFromSize, 100);
			$denominator = $denominator->divide($part2);
			$targetRadix = new BigNumber($abcToSize, 100);
			$part2 = $targetRadix->divide($denominator);
			// Clean from dot. Trim zeros at the end befor conversion.
			$part2 = str_replace('.', '', rtrim($part2, '0'));
			// Convert to target base.
			if ('10' !== (string)$toBase) {
				$part2 = self::convert($part2, 10, $toBase);
			}

			// Join parts and return
			return "{$prefix}{$part1}.{$part2}";
		}

		// OPTIMIZATION: Try to use native conversion functions
		//   for positional numeric systems
		else if ($positionalBase) {
			// Native PHP functions
			if (
				// Supported bases: between 2 and 36, inclusive
				isset(
					self::$posNumSystems[$fromBase],
					self::$posNumSystems[$toBase]
				)
				// Maximum integer is PHP_INT_MAX (platform dependent)
				&& strlen($number) <= self::$maxLengthForSpeedup[$fromBase]
			) {
				/**
				 * Special, super fast functions.
				 *
				 * - Support of negative integers is platform dependent. Without '-' char. Not acceptable
				 *   Maximum integer is PHP_INT_MAX (platform dependent)
				 * - Fractions are not supported
				 * + Ignores incorrect symbols
				 * + Fastest variant
				 */
				if ('10' == $fromBase) {
					switch ($toBase) {
						case '16': return $prefix . dechex($number);
						case '8':  return $prefix . decoct($number);
						case '2':  return $prefix . decbin($number);
					}
				} else if ('10' == $toBase) {
					switch ($fromBase) {
						case '16': return $prefix . hexdec($number);
						case '8':  return $prefix . octdec($number);
						case '2':  return $prefix . bindec($number);
					}
				}

				/**
				 * base_convert
				 * ------------
				 * - Negative integers are not supported
				 *   Maximum integer is PHP_INT_MAX+1 (platform dependent)
				 *   Supported bases: between 2 and 36, inclusive
				 * - Fractions are not supported
				 * + Ignores incorrect symbols
				 * - 40-70% slower than "special"
				 */
				return $prefix . base_convert($number, $fromBase, $toBase);
			}

			/**
			 * GMP extension functions
			 * ----------------------
			 * + Negative integers are fully supported
			 * + No maximum integer
			 * + Supported bases: from 2 to 62 (and -2 to -36 but not needed)
			 * - Fractions are not supported
			 * - Error on incorrect symbols
			 * - 70-215% slower than "special"
			 * - 70-90% slower than "base_convert"
			 */
			else if (self::$hasGmp) {
				return gmp_strval(gmp_init("{$prefix}{$number}", $fromBase), $toBase);
			}
		}

		// Pure PHP conversion
		return $prefix . self::rawConvert($number, $fromBase, $toBase);
	}


	/**
	 * PHP implementation of conversions
	 *
	 * @param string $number
	 * @param string $fromBase
	 * @param string $toBase
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	protected static function rawConvert($number, $fromBase, $toBase)
	{
		// Get bases settings: Alphabet in optimized format and alphabet length
		// NOTE: We use different alphabets for positional
		//       bases over an lower than 36 as GMP does
		// Source base settings
		if (isset(self::$posNumSystemsGmp[$fromBase])) {
			// Positional numeral system
			// Slice only needed part of alphabet to
			//   avoid errors on incorrect input
			if (($abcFromSize = $fromBase) > 36) {
				$abcFrom = self::$systems['_62'][1];
			} else {
				$abcFrom = self::$systems['_36'][1];
				$number  = strtolower($number);
			}
			$abcFrom = array_slice(
				$abcFrom, 0, $abcFromSize, true
			);
		} else {
			// Custom numeral system
			if (!isset(self::$systems[$fromBase][1])) {
				throw new Exception(
					"Unknown source number system [$fromBase]"
				);
			}
			$abcFrom     = self::$systems[$fromBase][1];
			$abcFromSize = strlen(self::$systems[$fromBase][0]);
		}
		// Target base settings
		if (isset(self::$posNumSystemsGmp[$toBase])) {
			// Positional numeral system
			$abcTo = ($abcToSize = $toBase) > 36
					? self::$systems['_62'][0]
					: self::$systems['_36'][0];
		} else {
			// Custom numeral system
			if (!isset(self::$systems[$toBase][0])) {
				throw new Exception(
					"Unknown target number system [$toBase]"
				);
			}
			$abcTo     = self::$systems[$toBase][0];
			$abcToSize = strlen(self::$systems[$toBase][0]);
		}

		$length = strlen($number = (string)$number);
		$result = '';

		// Prepare nibbles
		$nibbles = array();
		$i = 0;
		while($i < $length) {
			// Ignore incorrect chars
			isset($abcFrom[$char = $number[$i]])
				&& ($pos = $abcFrom[$char]) < $abcFromSize
				&& $nibbles[$i] = $pos;
			$i++;
		}
		$length = count($nibbles);

		// Main conversion
		do {
			$value = $newlen = $i = 0;
			while($i < $length) {
				if (($value = $value * $abcFromSize + $nibbles[$i])
				    >= $abcToSize
				) {
					$nibbles[$newlen++] = (int)($value / $abcToSize);
					$value %= $abcToSize;
				} else if ($newlen > 0) {
					$nibbles[$newlen++] = 0;
				}
				$i++;
			}
			$length = $newlen;
			$result = $abcTo[$value] . $result;
		} while ($newlen !== 0);

		return $result;
	}


	/**
	 * Adds new system for conversion
	 *
	 * @param string $name     System unique name
	 * @param string $alphabet System alphabet as a string
	 *
	 * @throws Exception
	 */
	public static function setSystem($name, $alphabet)
	{
		if (strlen($alphabet) < 2) {
			throw new Exception(
				"Two short alphabet for number system. You need at least 2 chars."
			);
		}

		self::$systems[$name] = array(
			$alphabet,
			array_flip(str_split($alphabet)),
			'noNegative' => false !== strpos($alphabet, '-'),
			'noFraction' => false !== strpos($alphabet, '.'),
		);
	}
}


// @codeCoverageIgnoreStart

// Check if we have GMP extension enabled
NumeralSystem::$hasGmp = defined('GMP_VERSION');

// Set max allowed numbers length for speedup with native functions by base
NumeralSystem::$maxLengthForSpeedup = 4 === PHP_INT_SIZE
	? array(2=>30,3=>19,4=>15,5=>13,6=>11,7=>11,8=>10,9=>9,10=>9,11=>8,12=>8,13=>8,14=>8,15=>7,16=>7,17=>7,18=>7,19=>7,20=>7,21=>7,22=>6,23=>6,24=>6,25=>6,26=>6,27=>6,28=>6,29=>6,30=>6,31=>6,32=>6,33=>6,34=>6,35=>6,36=>5)
	: array(2=>62,3=>39,4=>31,5=>27,6=>24,7=>22,8=>20,9=>19,10=>18,11=>18,12=>17,13=>17,14=>16,15=>16,16=>15,17=>15,18=>15,19=>14,20=>14,21=>14,22=>14,23=>13,24=>13,25=>13,26=>13,27=>13,28=>13,29=>12,30=>12,31=>12,32=>12,33=>12,34=>12,35=>12,36=>12)
;

// @codeCoverageIgnoreEnd
