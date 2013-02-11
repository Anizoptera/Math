<?php

namespace Aza\Components\Math\Tests;
use Aza\Components\Math\BigNumber;
use Aza\Components\Math\Exceptions\Exception;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionMethod;

/**
 * Testing number system conversion
 *
 * @project Anizoptera CMF
 * @package system.math
 *
 * @requires extension bcmath
 */
class BigNumberTest extends TestCase
{
	/**
	 * @var int
	 */
	protected $oldScale;

	protected function setUp()
	{
		$this->oldScale = BigNumber::getDefaultScale();
	}

	protected function tearDown()
	{
		BigNumber::setDefaultScale($this->oldScale);
	}



	/**
	 * Some overall tests, especially for constructor
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber
	 */
	public function testConstruct()
	{
		$number = new BigNumber('9,223,372,036,854,775.8080');
		//echo $number; // 9223372036854775.808
		$this->assertSame('9223372036854775.808', (string)$number);

		$bn1 = new BigNumber('9,223,372,036,854,775,808', 0);
		$this->assertSame('9223372036854775808', $bn1->getValue());
		$this->assertSame(0, $bn1->getCalcScale());

		$bn = new BigNumber("9,223 372`036'854,775.808000");
		$this->assertSame('9223372036854775.808', $bn->getValue());

		$bn2 = new BigNumber(2147483647);
		$this->assertSame('2147483647', $bn2->getValue());
		$this->assertSame($bn2::getDefaultScale(), $bn2->getCalcScale());

		$bn3 = new BigNumber($bn1, 4);
		$this->assertSame('9223372036854775808', $bn3->getValue());
		$this->assertSame(4, $bn3->getCalcScale());

		$bn4 = new BigNumber('9223372036854775808.12345678901', 5);
		$this->assertSame('9223372036854775808.12345', $bn4->getValue());
		$this->assertSame(5, $bn4->getCalcScale());

		$number = new BigNumber(true);
		$this->assertSame('1', $number->getValue());

		$number = new BigNumber(false);
		$this->assertSame('0', $number->getValue());

		$number = new BigNumber(null);
		$this->assertSame('0', $number->getValue());

		$number = new BigNumber('test');
		$this->assertSame('0', $number->getValue());

		$number = new BigNumber('');
		$this->assertSame('0', $number->getValue());

		BigNumber::setDefaultScale(2);
		$bn5 = new BigNumber(2147483647);
		$this->assertSame('2147483647', $bn5->getValue());
		$this->assertSame($bn2::getDefaultScale(), $bn5->getCalcScale());
	}

	/**
	 * Contructor exception case
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::__construct
	 * @covers Aza\Components\Math\Exceptions\Exception
	 * @expectedException \Aza\Components\Math\Exceptions\Exception
	 * @expectedExceptionMessage You need BCMath extension enabled to work with BigNumber
	 */
	public function testConstructException()
	{
		$hasBcmath = BigNumber::$hasBcmath;
		try {
			BigNumber::$hasBcmath = false;
			new BigNumber();
		} catch (Exception $e) {
			BigNumber::$hasBcmath = $hasBcmath;
			throw $e;
		}
		BigNumber::$hasBcmath = $hasBcmath;
	}

	/**
	 * String conversion tests
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::__toString
	 * @covers Aza\Components\Math\BigNumber::getValue
	 */
	public function testToString()
	{
		$bn = new BigNumber(2147483647);
		$this->assertEquals('2147483647', $bn);
		$this->assertSame('2147483647', (string)$bn);
		$this->assertSame('2147483647', $bn->__toString());

		// Floats can loose precision
		$bn = new BigNumber(23.4);
		$this->assertSame('23.4', (string)$bn->round(1));
		$this->assertSame('23.4', $bn->__toString());
	}


	/**
	 * Current scale tests
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::getScale
	 */
	public function testGetScale()
	{
		$bn = new BigNumber('5');
		$this->assertSame(-1, $bn->getScale());

		$bn = new BigNumber('5.5');
		$this->assertSame(1, $bn->getScale());

		$bn = new BigNumber('5');
		$bn->multiply(2.5);
		$this->assertSame(1, $bn->getScale());

		$bn = new BigNumber('23.5430');
		$this->assertSame(3, $bn->getScale());

		$bn = new BigNumber('23.5431');
		$this->assertSame(4, $bn->getScale());

		// Recurring fraction
		$bn = new BigNumber('5');
		$bn->divide(3);
		$this->assertSame($bn->getCalcScale(), $bn->getScale());
	}

	/**
	 * Calculation scale getter tests
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::getCalcScale
	 */
	public function testGetCalcScale()
	{
		$bn1 = new BigNumber('9223372036854775808');
		$bn2 = new BigNumber(2147483647, 20);

		$this->assertSame(100, $bn1->getCalcScale());
		$this->assertSame(20, $bn2->getCalcScale());
	}

	/**
	 * Value getter tests
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::getValue
	 */
	public function testGetValue()
	{
		$bn1 = new BigNumber('9223372036854775808');
		$bn2 = new BigNumber('2147483647.9223372036854775808', 10);

		$this->assertSame('9223372036854775808', $bn1->getValue());
		$this->assertSame('2147483647.9223372036', $bn2->getValue());
	}


	/**
	 * Calculation scale setter tests
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::setCalcScale
	 */
	public function testSetCalcScale()
	{
		$bn = new BigNumber('9223372036854775808');

		$this->assertSame($bn, $bn->setCalcScale(6));
		$this->assertSame(6, $bn->getCalcScale());
	}

	/**
	 * Value setter tests
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::setValue
	 */
	public function testSetValue()
	{
		$bn = new BigNumber(1, 0);

		$this->assertSame($bn, $bn->setValue(1234.657));
		$this->assertSame('1234', $bn->getValue());
	}

	/**
	 * Value setter + set scale tests
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::setValue
	 */
	public function testSetValueWithScale()
	{
		$bn = new BigNumber(1);
		$bn->setCalcScale(2);

		$this->assertSame($bn, $bn->setValue(1234.657));
		$this->assertSame('1234.65', $bn->getValue());
		$this->assertSame(2, $bn->getCalcScale());
	}



	/**
	 * Test abs
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::abs
	 */
	public function testAbs()
	{
		$number = new BigNumber('-18446744073709551617');
		$result = $number->abs();
		$this->assertSame('18446744073709551617', (string)$result);

		$number = new BigNumber('-18446744073709551617');
		$result = $number->abs();
		$this->assertSame('18446744073709551617', (string)$result);

		$number = new BigNumber('-1');
		$result = $number->abs();
		$this->assertSame('1', (string)$result);

		$number = new BigNumber('1');
		$result = $number->abs();
		$this->assertSame('1', (string)$result);

		$number = new BigNumber('-1234.5678');
		$result = $number->abs();
		$this->assertSame('1234.5678', (string)$result);

		$number = new BigNumber('5678');
		$result = $number->abs();
		$this->assertSame('5678', (string)$result);

		$number = new BigNumber('0');
		$result = $number->abs();
		$this->assertSame('0', (string)$result);

		$number = new BigNumber('+0');
		$result = $number->abs();
		$this->assertSame('0', (string)$result);

		$number = new BigNumber('');
		$result = $number->abs();
		$this->assertSame('0', (string)$result);
	}


	/**
	 * Test add
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::add
	 */
	public function testAdd()
	{
		// ----
		$bn = new BigNumber(2147483647);

		$this->assertSame($bn, $bn->add('9223372036854775808'));
		$this->assertSame('9223372039002259455', $bn->getValue());

		$bn->setCalcScale(3);
		$this->assertSame('9223372039002259457.25', $bn->add('2.25')->getValue());


		// ----
		// Floats can loose precision
		$number = new BigNumber('2.33');
		$bn = $number->add('4.1');
		$this->assertSame('6.43', (string)$bn->round(2));

		$number = new BigNumber('2.33');
		$bn = $number->add('4.123');
		$this->assertSame('6.453', (string)$bn->round(3));


		// ----
		$x = new BigNumber('18446744073709551615');
		$y = new BigNumber(        '100000000000');

		$a = (string)$y;
		$b = (string)$x;
		$a = $x->add($a);
		$b = $y->add($b);

		$this->assertTrue($a->isEqualTo($b));
		$this->assertTrue($b->isEqualTo($a));

		$this->assertSame('18446744173709551615', (string)$a);
		$this->assertSame((string)$a, (string)$b);

	}

	/**
	 * Test subtract
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::subtract
	 */
	public function testSubtract()
	{
		// ----
		$bn = new BigNumber(2147483647);

		$this->assertSame($bn, $bn->subtract('9223372036854775808'));
		$this->assertSame('-9223372034707292161', $bn->getValue());

		$bn->setCalcScale(3);
		$this->assertSame('-9223372034707292163.25', $bn->subtract('2.25')->getValue());


		// ----
		// Floats can loose precision
		$number = new BigNumber('6.43');
		$bn = $number->subtract('2.2');
		$this->assertSame('4.23', (string)$bn->round(2));

		$number = new BigNumber('6.453');
		$bn = $number->subtract('2.33');
		$this->assertSame('4.123', (string)$bn->round(3));


		// ----
		$x = new BigNumber('18446744073709551618');
		$y = new BigNumber(       '4000000000000');
		$this->assertSame('18446740073709551618', (string)$x->subtract($y));
	}

	/**
	 * Test multiply
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::multiply
	 */
	public function testMultiply()
	{
		// ----
		$bn1 = new BigNumber('9223372036854775808.34747474747474747', 17);
		$bn2 = new BigNumber('9223372036854775808.34747474747474747', 17);
		$bn3 = new BigNumber('9223372036854775808.34747474747474747', 17);

		$bn1->setCalcScale(0);
		$this->assertSame($bn1, $bn1->multiply(35));
		$this->assertSame('322818021289917153292', $bn1->getValue());

		$bn2->setCalcScale(3);
		$this->assertSame($bn2, $bn2->multiply(35));
		$this->assertSame('322818021289917153292.161', $bn2->getValue());

		$this->assertSame($bn3, $bn3->multiply(35));
		$this->assertSame('322818021289917153292.16161616161616145', $bn3->getValue());


		// ----
		$x = new BigNumber('8589934592');           // 2**33
		$y = new BigNumber('36893488147419103232'); // 2**65

		$a = (string)$y;
		$b = (string)$x;
		$a = $x->multiply($a); // 2**98
		$b = $y->multiply($b); // 2**98

		$this->assertTrue($a->isEqualTo($b));
		$this->assertTrue($b->isEqualTo($a));

		$this->assertSame('316912650057057350374175801344', (string)$a);
		$this->assertSame('316912650057057350374175801344', (string)$b);
	}

	/**
	 * Test divide
	 *
	 * @author amal
	 * @group functional
	 * @covers Aza\Components\Math\BigNumber::divide
	 * @covers Aza\Components\Math\BigNumber::mod
	 */
	public function testDivide()
	{
		// ----
		$number = new BigNumber(6, 100);
		$result = $number->divide(2);
		$this->assertSame('3', (string)$result);

		// ----
		$x = '1180591620717411303425'; // 2**70 + 1
		$y = '12345678910';
		$number = new BigNumber($x, 10);
		$result = $number->divide($y);
		$this->assertSame('95627922070.8657895449', (string)$result);

		// ----
		$x = '1180591620717411303425';
		$y = '12345678910';
		$number = new BigNumber($x, 3);
		$result = $number->divide($y);
		$this->assertSame('95627922070.865', (string)$result);

		// ----
		$x = '1180591620717411303425';
		$y = '12345678910';
		$number = new BigNumber($x, 2);
		$result = $number->divide($y);
		$this->assertSame('95627922070.86', (string)$result);


		$bn = new BigNumber('9223372036854775808');
		$this->assertSame($bn, $bn->divide(2));
		$this->assertSame('4611686018427387904', $bn->getValue());

		$bn->setCalcScale(5);
		$this->assertSame('1537228672809129301.33333', $bn->divide(3)->getValue());


		// ----
		$n1  = '1180591620717411303425';
		$n2  = '12345678910';
		$mod = '10688759725';
		$fraction_part = '8657895449';
		$number = new BigNumber($n1, 11);
		$x = $number->divide($n2);
		$this->assertSame("95627922070.{$fraction_part}", (string)$x);

		$number = new BigNumber($n1);
		$x = $number->mod($n2);
		$this->assertSame($mod, (string)$x);

		$x->setCalcScale(11)->divide($n2);
		$this->assertSame("0.{$fraction_part}", (string)$x);


		// ----
		// Create new big number with the specified precision for operations (20)
		$number = new BigNumber('118059162071741130342591466421', 20);
		// Divide number
		$number->divide(12345678910);
		// See results
		//echo $number; // 9562792207086578954.49764831288650451382
		$this->assertSame("9562792207086578954.49764831288650451382", (string)$number);
	}

	/**
	 * Test divide by zero
	 *
	 * @author amal
	 * @group functional
	 * @covers Aza\Components\Math\BigNumber::divide
	 * @covers Aza\Components\Math\Exceptions\Exception
	 * @expectedException \Aza\Components\Math\Exceptions\Exception
	 * @expectedExceptionMessage Division by zero
	 */
	public function testDivideByZero()
	{
		$number = new BigNumber('9223372036854775808');
		$number->divide(0);
	}


	/**
	 * Test mod
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::mod
	 */
	public function testMod()
	{
		$bn = new BigNumber('9223372036854775808');

		$this->assertSame($bn, $bn->mod(3));
		$this->assertSame('2', $bn->getValue());
		$this->assertSame('0', $bn->mod(2)->getValue());
	}

	/**
	 * Test mod division by zero
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::mod
	 * @covers Aza\Components\Math\Exceptions\Exception
	 * @expectedException \Aza\Components\Math\Exceptions\Exception
	 * @expectedExceptionMessage Division by zero
	 */
	public function testModDivisionByZero()
	{
		$bn = new BigNumber('9223372036854775808');
		$bn->mod(0);
	}

	/**
	 * Test pow
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::pow
	 */
	public function testPow()
	{
		$bn1 = new BigNumber(16);
		$bn2 = new BigNumber('4294967296.5352423424523', 13);

		$this->assertSame($bn1, $bn1->pow(8));
		$this->assertSame('4294967296', $bn1->getValue());

		$bn2->setCalcScale(6);
		$this->assertSame('18446744078307248328.820606', $bn2->pow(2)->getValue());
	}

	/**
	 * Test pow mod
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::powMod
	 */
	public function testPowMod()
	{
		// ----
		$bn1 = new BigNumber(16);
		$bn2 = clone $bn1;

		$this->assertEquals($bn2, $bn1);
		$this->assertNotSame($bn2, $bn1);

		$this->assertSame($bn2, $bn2->powMod(8, 2));
		$this->assertSame('0', $bn2->getValue());
		$this->assertNotEquals($bn2, $bn1);

		$this->assertSame('0', $bn1->powMod(8, 2)->getValue());
		$this->assertEquals($bn2, $bn1);


		// ----
		$bn1 = new BigNumber(10);
		$bn1 = $bn1->powMod(20, 30);
		$this->assertSame('10', (string)$bn1);

		$bn2 = new BigNumber(10);
		$bn2 = $bn2->pow(20)->mod(30);
		$this->assertSame('10', (string)$bn2);
		$this->assertSame((string)$bn1, (string)$bn2);
	}

	/**
	 * Test pow mod division by zero
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::powMod
	 * @covers Aza\Components\Math\Exceptions\Exception
	 * @expectedException \Aza\Components\Math\Exceptions\Exception
	 * @expectedExceptionMessage Division by zero
	 */
	public function testPowModDivisionByZero()
	{
		$bn = new BigNumber(16);
		$bn->powMod(8, 0);
	}


	/**
	 * Test sqrt
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::sqrt
	 */
	public function testSqrt()
	{
		$bn1 = new BigNumber(16);
		$bn2 = new BigNumber(17);
		$bn3 = clone $bn2;

		$this->assertSame($bn1, $bn1->sqrt());
		$this->assertSame('4', $bn1->getValue());
		$this->assertSame(
			'4',
			$bn2->setCalcScale(0)->sqrt()->getValue()
		);

		$this->assertSame(
			'4.12310562',
			$bn3->setCalcScale(8)->sqrt()->getValue()
		);
	}


	/**
	 * Test shiftLeft
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::shiftLeft
	 */
	public function testShiftLeft()
	{
		$bn = new BigNumber(1);

		$this->assertSame($bn, $bn->shiftLeft(30));
		$this->assertSame('1073741824', $bn->getValue());
		$this->assertSame('4611686018427387904', $bn->shiftLeft(32)->getValue());
		$this->assertSame('42535295865117307932921825928971026432', $bn->shiftLeft(63)->getValue());
		$this->assertSame('784637716923335095479473677900958302012794430558004314112', $bn->shiftLeft(64)->getValue());
		$this->assertSame('3369993333393829974333376885877453834204643052817571560137951281152', $bn->shiftLeft(32)->getValue());
	}

	/**
	 * Test shiftRight
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::shiftRight
	 */
	public function testShiftRight()
	{
		$bn = new BigNumber('3369993333393829974333376885877453834204643052817571560137951281152');

		$this->assertSame($bn, $bn->shiftRight(32));
		$this->assertSame('784637716923335095479473677900958302012794430558004314112', $bn->getValue());
		$this->assertSame('42535295865117307932921825928971026432', $bn->shiftRight(64)->getValue());
		$this->assertSame('4611686018427387904', $bn->shiftRight(63)->getValue());
		$this->assertSame('1073741824', $bn->shiftRight(32)->getValue());
		$this->assertSame('1', $bn->shiftRight(30)->getValue());
	}


	/**
	 * Test round
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::round
	 */
	public function testRound()
	{
		// ROUND_HALF_UP
		$_bn = new BigNumber('3.4');
		$bn = $_bn->round();
		$this->assertSame('3', (string)$bn);

		$_bn = new BigNumber('3.4');
		$bn = $_bn->round(-1);
		$this->assertSame('3', (string)$bn);

		$_bn = new BigNumber('3.4');
		$bn = $_bn->round(0);
		$this->assertSame('3', (string)$bn);

		$_bn = new BigNumber('3.4');
		$bn = $_bn->round(1);
		$this->assertSame('3.4', (string)$bn);

		$_bn = new BigNumber('3.5');
		$bn = $_bn->round();
		$this->assertSame('4', (string)$bn);

		$_bn = new BigNumber('3.6');
		$bn = $_bn->round();
		$this->assertSame('4', (string)$bn);

		$_bn = new BigNumber('1.95583');
		$bn = $_bn->round();
		$this->assertSame('2', (string)$bn);

		$_bn = new BigNumber('1.95583');
		$bn = $_bn->round(1);
		$this->assertSame('2', (string)$bn);

		$_bn = new BigNumber('1.95583');
		$bn = $_bn->round(2);
		$this->assertSame('1.96', (string)$bn);

		$_bn = new BigNumber('1.95583');
		$bn = $_bn->round(3);
		$this->assertSame('1.956', (string)$bn);

		$_bn = new BigNumber('1.95583');
		$bn = $_bn->round(4);
		$this->assertSame('1.9558', (string)$bn);

		$_bn = new BigNumber('1.95583');
		$bn = $_bn->round(5);
		$this->assertSame('1.95583', (string)$bn);

		$_bn = new BigNumber('1241757');
		$bn = $_bn->round();
		$this->assertSame('1241757', (string)$bn);

		$_bn = new BigNumber('1241757');
		$bn = $_bn->round(5);
		$this->assertSame('1241757', (string)$bn);

		$_bn = new BigNumber('-3.4');
		$bn = $_bn->round();
		$this->assertSame('-3', (string)$bn);

		$_bn = new BigNumber('-3.5');
		$bn = $_bn->round();
		$this->assertSame('-3', (string)$bn);

		$_bn = new BigNumber('-3.6');
		$bn = $_bn->round();
		$this->assertSame('-4', (string)$bn);

		$_bn = new BigNumber(5);
		$bn = $_bn->divide(3)->round(2);
		$this->assertSame('1.67', (string)$bn);

		$_bn = new BigNumber(5);
		$bn = $_bn->divide(3)->round(3);
		$this->assertSame('1.667', (string)$bn);

		$_bn = new BigNumber(5);
		$bn = $_bn->divide(3)->round();
		$this->assertSame('2', (string)$bn);

		$_bn = new BigNumber('123456.7456713');
		$bn = $_bn->round(6);
		$this->assertSame('123456.745671', (string)$bn);

		$_bn = new BigNumber('1.11');
		$bn = $_bn->round(0);
		$this->assertSame('1', (string)$bn);

		$_bn = new BigNumber('1.11');
		$bn = $_bn->round(2);
		$this->assertSame('1.11', (string)$bn);

		$_bn = new BigNumber('0.1666666666666665');
		$bn = $_bn->round(13);
		$this->assertSame('0.1666666666667', (string)$bn);

		$_bn = new BigNumber('0.1666666666666665');
		$bn = $_bn->round(0.13);
		$this->assertSame('0', (string)$bn);

		$_bn = new BigNumber('9.999');
		$bn = $_bn->round();
		$this->assertSame('10', (string)$bn);

		$_bn = new BigNumber('9.999');
		$bn = $_bn->round(2);
		$this->assertSame('10', (string)$bn);


		// ROUND_HALF_DOWN
		$mode = BigNumber::ROUND_HALF_DOWN;
		$_bn = new BigNumber('3.5');
		$bn = $_bn->round(0, $mode);
		$this->assertSame('3', (string)$bn);

		$_bn = new BigNumber('-3.5');
		$bn = $_bn->round(0, $mode);
		$this->assertSame('-4', (string)$bn);

		$_bn = new BigNumber('-3.55');
		$bn = $_bn->round(1, $mode);
		$this->assertSame('-3.6', (string)$bn);

		$_bn = new BigNumber('1.95583');
		$bn = $_bn->round(2, $mode);
		$this->assertSame('1.96', (string)$bn);

		$_bn = new BigNumber('1.95583');
		$bn = $_bn->round(3, $mode);
		$this->assertSame('1.956', (string)$bn);


		// ROUND_CUT
		$mode = BigNumber::ROUND_CUT;
		$_bn = new BigNumber('3.9');
		$bn = $_bn->round(0, $mode);
		$this->assertSame('3', (string)$bn);

		$_bn = new BigNumber('3.1');
		$bn = $_bn->round(0, $mode);
		$this->assertSame('3', (string)$bn);

		$_bn = new BigNumber('-3.9');
		$bn = $_bn->round(0, $mode);
		$this->assertSame('-3', (string)$bn);

		$_bn = new BigNumber('-3.1');
		$bn = $_bn->round(0, $mode);
		$this->assertSame('-3', (string)$bn);

		$_bn = new BigNumber('9.99');
		$bn = $_bn->round(1, $mode);
		$this->assertSame('9.9', (string)$bn);

		$_bn = new BigNumber('-9.99');
		$bn = $_bn->round(1, $mode);
		$this->assertSame('-9.9', (string)$bn);

		$_bn = new BigNumber('-9.9');
		$bn = $_bn->round(1, $mode);
		$this->assertSame('-9.9', (string)$bn);
	}

	/**
	 * Test floor
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::floor
	 */
	public function testFloor()
	{
		$bn1 = new BigNumber('4.3', 1);
		$bn2 = new BigNumber('9.999', 3);
		$bn3 = new BigNumber('-3.14', 2);
		$bn4 = new BigNumber('23.00000000000000999999', 20);
		$bn5 = new BigNumber('23.00000000000001999999', 20);
		$bn6 = new BigNumber('-23.00000000000000999999', 20);
		$bn7 = new BigNumber('-23.00000000000001999999', 20);

		$this->assertSame('4', $bn1->floor()->getValue());
		$this->assertSame('9', $bn2->floor()->getValue());
		$this->assertSame('-4', $bn3->floor()->getValue());
		$this->assertSame('23', $bn4->floor()->getValue());
		$this->assertSame('23', $bn5->floor()->getValue());
		$this->assertSame('-23', $bn6->floor()->getValue());
		$this->assertSame('-24', $bn7->floor()->getValue());
	}

	/**
	 * Test ceil
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::ceil
	 */
	public function testCeil()
	{
		$bn1 = new BigNumber('4.3', 1);
		$bn2 = new BigNumber('9.999', 3);
		$bn3 = new BigNumber('-3.14', 2);
		$bn4 = new BigNumber('23.00000000000000999999', 20);
		$bn5 = new BigNumber('23.00000000000001999999', 20);
		$bn6 = new BigNumber('-23.00000000000000999999', 20);
		$bn7 = new BigNumber('-23.00000000000001999999', 20);

		$this->assertSame('5', $bn1->ceil()->getValue());
		$this->assertSame('10', $bn2->ceil()->getValue());
		$this->assertSame('-3', $bn3->ceil()->getValue());
		$this->assertSame('23', $bn4->ceil()->getValue());
		$this->assertSame('24', $bn5->ceil()->getValue());
		$this->assertSame('-23', $bn6->ceil()->getValue());
		$this->assertSame('-23', $bn7->ceil()->getValue());
	}


	/**
	 * Test increment
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::increment
	 */
	public function testIncrement()
	{
		$bn = new BigNumber('9223372036854775808');

		$this->assertSame($bn, $bn->increment());
		$this->assertSame('9223372036854775809', $bn->getValue());

		$this->assertSame('9223372036854775810', $bn->increment()->getValue());
	}

	/**
	 * Test decrement
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::decrement
	 */
	public function testDecrement()
	{
		$bn = new BigNumber('9223372036854775808');

		$this->assertSame($bn, $bn->decrement());
		$this->assertSame('9223372036854775807', $bn->getValue());

		$this->assertSame('9223372036854775806', $bn->decrement()->getValue());
	}


	/**
	 * Test compareTo
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::compareTo
	 */
	public function testCompareTo()
	{
		$bn1 = new BigNumber('9223372036854775808');
		$bn2 = new BigNumber(2147483647);

		$this->assertSame(0, $bn1->compareTo('9223372036854775808'));
		$this->assertSame(0, $bn2->compareTo(2147483647));
		$this->assertSame(1, $bn1->compareTo($bn2));
		$this->assertSame(-1, $bn2->compareTo($bn1));

		$number = new BigNumber(10);
		$this->assertTrue($number->compareTo(20) < 0);
		$this->assertTrue($number->isLessThan(20));
		$this->assertTrue($number->isLessThanOrEqualTo(20));

		$number = new BigNumber(20);
		$this->assertTrue($number->compareTo(10) > 0);
		$this->assertTrue($number->isGreaterThan(10));
		$this->assertTrue($number->isGreaterThanOrEqualTo(10));

		$number = new BigNumber(20);
		$this->assertTrue($number->compareTo(20) === 0);
		$this->assertTrue($number->isLessThanOrEqualTo(20));
		$this->assertTrue($number->isGreaterThanOrEqualTo(20));
	}

	/**
	 * Test isEqualTo
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::isEqualTo
	 */
	public function testIsEqualTo()
	{
		$bn1 = new BigNumber('9223372036854775808.123456', 6);
		$bn2 = new BigNumber('9223372036854775808.123461', 6);

		$this->assertFalse($bn1->isEqualTo($bn2));

		$bn1->setCalcScale(4);
		$this->assertTrue($bn1->isEqualTo($bn2));

		$n = '18446744073709551616';
		$_bn = new BigNumber($n);
		$this->assertTrue($_bn->isEqualTo($n));
	}

	/**
	 * Test isGreaterThan
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::isGreaterThan
	 */
	public function testIsGreaterThan()
	{
		$bn1 = new BigNumber('9223372036854775808.123456', 6);
		$bn2 = new BigNumber('9223372036854775808.123461', 6);

		$this->assertTrue($bn2->isGreaterThan($bn1));
		$this->assertFalse($bn1->isGreaterThan($bn2));

		$bn2->setCalcScale(4);
		$this->assertFalse($bn2->isGreaterThan($bn1));

		$bn1->setCalcScale(4);
		$this->assertFalse($bn1->isGreaterThan($bn2));
	}

	/**
	 * Test isGreaterThanOrEqualTo
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::isGreaterThanOrEqualTo
	 */
	public function testIsGreaterThanOrEqualTo()
	{
		$bn1 = new BigNumber('9223372036854775808.123456', 6);
		$bn2 = new BigNumber('9223372036854775808.123461', 6);

		$this->assertTrue($bn2->isGreaterThanOrEqualTo($bn1));
		$this->assertFalse($bn1->isGreaterThanOrEqualTo($bn2));

		$bn2->setCalcScale(4);
		$this->assertTrue($bn2->isGreaterThanOrEqualTo($bn1));

		$bn1->setCalcScale(4);
		$this->assertTrue($bn1->isGreaterThanOrEqualTo($bn2));
	}

	/**
	 * Test isLessThan
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::isLessThan
	 */
	public function testIsLessThan()
	{
		$bn1 = new BigNumber('9223372036854775808.123456', 6);
		$bn2 = new BigNumber('9223372036854775808.123461', 6);

		$this->assertTrue($bn1->isLessThan($bn2));
		$this->assertFalse($bn2->isLessThan($bn1));

		$bn1->setCalcScale(4);
		$this->assertFalse($bn1->isLessThan($bn2));

		$bn2->setCalcScale(4);
		$this->assertFalse($bn2->isLessThan($bn1));
	}

	/**
	 * Test isLessThanOrEqualTo
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::isLessThanOrEqualTo
	 */
	public function testIsLessThanOrEqualTo()
	{
		$bn1 = new BigNumber('9223372036854775808.123456', 6);
		$bn2 = new BigNumber('9223372036854775808.123461', 6);

		$this->assertTrue($bn1->isLessThanOrEqualTo($bn2));
		$this->assertFalse($bn2->isLessThanOrEqualTo($bn1));

		$bn1->setCalcScale(4);
		$this->assertTrue($bn1->isLessThanOrEqualTo($bn2));

		$bn2->setCalcScale(4);
		$this->assertTrue($bn2->isLessThanOrEqualTo($bn1));
	}


	/**
	 * Test signum
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::signum
	 */
	public function testSignum()
	{
		$bn1 = new BigNumber(1234);
		$bn2 = new BigNumber(-1234);
		$bn3 = new BigNumber(0);
		$bn4 = new BigNumber('0.0000005', 7);
		$bn5 = new BigNumber('-0.0000005', 7);

		$this->assertSame(1, $bn1->signum());
		$this->assertSame(-1, $bn2->signum());
		$this->assertSame(0, $bn3->signum());

		$bn4->setCalcScale(0);
		$this->assertSame(0, $bn4->signum(0));

		$bn4->setCalcScale(7);
		$this->assertSame(1, $bn4->signum());

		$bn4->setCalcScale(7);
		$this->assertSame(-1, $bn5->signum());
	}


	/**
	 * Test isNegative
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::isNegative
	 */
	public function testIsNegative()
	{
		$bn1 = new BigNumber(1234);
		$bn2 = new BigNumber(-1234);
		$bn3 = new BigNumber(0);

		$this->assertFalse($bn1->isNegative());
		$this->assertTrue($bn2->isNegative());
		$this->assertFalse($bn3->isNegative());

		$bn4 = new BigNumber('-0.0000', 3);
		$bn5 = new BigNumber('-0.0001', 3);
		$bn6 = new BigNumber('-0.0001', 4);

		$this->assertTrue($bn4->isNegative());
		$this->assertTrue($bn5->isNegative());
		$this->assertTrue($bn6->isNegative());

		$this->assertFalse($bn4->add(0)->isNegative());
		$this->assertFalse($bn5->add(0)->isNegative());
		$this->assertTrue($bn6->add(0)->isNegative());
	}

	/**
	 * Test isPositive
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::isPositive
	 */
	public function testIsPositive()
	{
		$bn1 = new BigNumber(1234);
		$bn2 = new BigNumber(-1234);
		$bn3 = new BigNumber(0);

		$this->assertTrue($bn1->isPositive());
		$this->assertFalse($bn2->isPositive());
		$this->assertTrue($bn3->isPositive());

		$bn4 = new BigNumber('-0.0000', 3);
		$bn5 = new BigNumber('-0.0001', 3);
		$bn6 = new BigNumber('-0.0001', 4);

		$this->assertFalse($bn4->isPositive());
		$this->assertFalse($bn5->isPositive());
		$this->assertFalse($bn6->isPositive());

		$this->assertTrue($bn4->add(0)->isPositive());
		$this->assertTrue($bn5->add(0)->isPositive());
		$this->assertFalse($bn6->add(0)->isPositive());
	}


	/**
	 * Test negate
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::negate
	 */
	public function testNegate()
	{
		$bn1 = new BigNumber(1234);
		$bn2 = new BigNumber('0.000000567', 9);

		$this->assertSame('-1234', $bn1->negate()->getValue());
		$this->assertSame('1234', $bn1->negate()->getValue());

		$bn2->setCalcScale(7);
		$this->assertSame('-0.0000005', $bn2->negate()->getValue());

		$bn2->setCalcScale(6);
		$this->assertSame('0', $bn2->negate()->getValue());
	}


	/**
	 * Test convertToBase
	 *
	 * @author amal
	 * @group functional
	 * @covers Aza\Components\Math\BigNumber::convertToBase
	 */
	public function testConvertToBase()
	{
		$bn1 = new BigNumber('9223372036854775807');
		$bn2 = new BigNumber('9223372036854775810');
		$bn3 = new BigNumber('2');

		$this->assertSame('7fffffffffffffff', $bn1->convertToBase(16));
		$this->assertSame('8000000000000002', $bn2->convertToBase(16));
		$this->assertSame('10', $bn3->convertToBase(2));
		$this->assertSame('1y2p0ij32e8e7', $bn1->convertToBase(36));


		$number = new BigNumber('9223372036854775807');
		$number = $number->pow(2)->convertToBase(62);
//		echo $number . PHP_EOL; // 1wlVYJaWMuw53lV7Cg98qn
		$this->assertSame('1wlVYJaWMuw53lV7Cg98qn', $number);
	}


	/**
	 * Test setDefaultScale
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::setDefaultScale
	 */
	public function testSetDefaultScale()
	{
		BigNumber::setDefaultScale(23);
		$this->assertSame(23, BigNumber::getDefaultScale());
	}


	/**
	 * Test filterNumber
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::__construct
	 * @covers Aza\Components\Math\BigNumber::setValue
	 * @covers Aza\Components\Math\BigNumber::filterNumber
	 */
	public function testFilterNumber()
	{
		$bn1 = new BigNumber(0);
		$bn2 = new BigNumber(2147483647);

		$ref = new ReflectionMethod($bn1, 'filterNumber');
		$ref->setAccessible(true);


		// ----
		$this->assertSame(
			'1234',
			$ref->invoke($bn1, 1234)
		);

		// ----
		$this->assertSame(
			'1234567890.1234',
			$ref->invoke($bn1, '1234567890.1234')
		);

		// ----
		$this->assertSame(
			'9223372036854775808',
			$ref->invoke($bn1, '9,223,372,036,854,775,808')
		);

		// ----
		$this->assertSame(
			'9223372036854775808.432',
			$ref->invoke($bn1, '9,223,372,036,854,775,808.432')
		);

		// ----
		$this->assertSame(
			'2147483647',
			$ref->invoke($bn1, $bn2)
		);
	}

	/**
	 * Test prepareFloat
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::__construct
	 * @covers Aza\Components\Math\BigNumber::setValue
	 * @covers Aza\Components\Math\BigNumber::filterNumber
	 */
	public function testPrepareFloat()
	{
		// ----
		$bn = new BigNumber(12e-6);
		$this->assertSame('0.000012', (string)$bn);

		// ----
		$bn = new BigNumber(12e-16);
		$this->assertSame('0.0000000000000012', (string)$bn);

		// ----
		$expected = '0.0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000012';
		$bn = new BigNumber(12e-100);
		$this->assertSame($expected, (string)$bn);
		$bn = new BigNumber("12e-100");
		$this->assertSame($expected, (string)$bn);

		// ----
		$bn = new BigNumber(12e6);
		$this->assertSame('12000000', (string)$bn);

		// ----
		$bn = new BigNumber(12e16);
		$this->assertSame('120000000000000000', (string)$bn);

		// ----
		$expected = '120000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';
		$bn = new BigNumber(12e100);
		$this->assertSame($expected, (string)$bn);
		$bn = new BigNumber("12e100");
		$this->assertSame($expected, (string)$bn);
		$bn = new BigNumber("12E100");
		$this->assertSame($expected, (string)$bn);

		// ----
		$bn = new BigNumber(0.12);
		$this->assertSame('0.12', (string)$bn);

		// ----
		$bn = new BigNumber(1.2);
		$this->assertSame('1.2', (string)$bn);

		// ----
		$bn = new BigNumber(6.43);
		$this->assertSame('6.43', (string)$bn);

		// ----
		$bn = new BigNumber(9.5678);
		$this->assertSame('9.5678', (string)$bn);

		// ----
		$number = new BigNumber(2.33);
		$bn = $number->add(4.1);
		$this->assertSame("6.43", (string)$bn->round(2));

		$number = new BigNumber(2.33);
		$bn = $number->add(4.123);
		$this->assertSame("6.453", (string)$bn->round(3));
	}

	/**
	 * Test trim
	 *
	 * @author amal
	 * @group unit
	 * @covers Aza\Components\Math\BigNumber::trim
	 */
	public function testTrim()
	{
		$data = array(
			array('1000', '1000'),
			array('1324546674576580', '1324546674576580'),
			array('13245466745765801324546674576580', '13245466745765801324546674576580'),
			array(
				'13245466745765801324546674576580.000000000000000000000010000000000000000000000000000000',
				'13245466745765801324546674576580.00000000000000000000001',
			),
			array(
				'2.00000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
				'2',
			),
			array(
				'2.00000000000000000000000000000000000000000010000000000000000000000000000000000000000000',
				'2.0000000000000000000000000000000000000000001',
			),
			array('1000.0000', '1000'),
			array('1000.1000', '1000.1'),
			array('1000.01000', '1000.01'),
			array('1000.0001', '1000.0001'),
			array('0.0000120000000000000', '0.000012'),
			array('1.2500000000', '1.25'),
			array('100.0000', '100'),
			array('1230.00000000', '1230'),
		);

		$bn = new BigNumber();
		$ref = new ReflectionMethod($bn, 'trim');
		$ref->setAccessible(true);

		foreach ($data as $d) {
			list($original, $expected) = $d;
			$result = $ref->invoke($bn, $original);
			$this->assertSame($expected, $result);
		}
	}



	/**
	 * Tests the possibility of a "negative" string zero, i.e. "-0.000"
	 *
	 * The sign of -0 is still a negative sign. This is ultimately calculated
	 * by bccomp(), according to which, when -0.000 is compared to 0.000, it
	 * will return a -1, meaning -0.000 is less than 0.000, but -0 compared to
	 * 0 will return a 0, meaning the two are equal. This is odd, but it is the
	 * expected behavior.
	 *
	 * @author amal
	 * @group unit
	 * @coversNothing
	 */
	public function testNegativeZero()
	{
		$bn = new BigNumber('-0.0000005', 3);

		$this->assertSame('-0', $bn->getValue());
		$this->assertSame(-1, $bn->signum());
		$this->assertTrue($bn->isNegative());

		$bn = new BigNumber('-00.0', 3);
		$this->assertSame('-0', $bn->getValue());
		$this->assertSame('0', $bn->add(0)->getValue());

		$bn = new BigNumber('-0', 5);
		$this->assertSame('0', $bn->getValue());
		$this->assertSame('0', (string)$bn->add(0));
	}
}
