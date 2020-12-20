<?php
use vndor\Foundation\Validator;
use vndor\Foundation\AException;

class MockValidator extends Validator
{
	static $mockInstance;
	public function validate($validation, $value = null, $exceptionText = null, $traceLevel = 1 )
	{
		try {
			return parent::validate($validation, $value, $exceptionText, $traceLevel);
		}  catch (AException $e) {
			return $e->getMessage();
		}
	}
	public function parseVariable($text)
	{
		return parent::parseVariable($text);
	}
}

function validate($validation, $value = null, $exceptionText = null)
{
	if (!MockValidator::$mockInstance) {
		MockValidator::$mockInstance = new MockValidator();
	}
	return MockValidator::$mockInstance->validate( $validation, $value, $exceptionText, 2 );
}

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
	protected function lfstring($str){
		return str_replace("\r", '', $str);
	}

	public function testParseVariable()
	{
		$m = new MockValidator();

		$t = $m->parseVariable('\Static\Class::$value = validate(function($v){return is_file($v);}, 0);');
		$this->assertEquals('\Static\Class::$value', $t);

		$t = $m->parseVariable('$value = validate(function($v){return is_file($v);}, 0);');
		$this->assertEquals('$value', $t);

		$t = $m->parseVariable('$obj->value = validate(function($v){return is_file($v);}, 0);');
		$this->assertEquals('$obj->value', $t);

		$t = $m->parseVariable('$obj->value->getValue()->value = validate(function($v){return is_file($v);}, 0);');
		$this->assertEquals('$obj->value->getValue()->value', $t);

		$t = $m->parseVariable('$obj->value->getValue("hello world")->value = validate(function($v){return is_file($v);}, 0);');
		$this->assertEquals('$obj->value->getValue("hello world")->value', $t);

		$t = $m->parseVariable('validate(function($v){return is_file($v);}, 0);');
		$this->assertEquals(false, $t);

		$t = $m->parseVariable('$exText = MockValidator::$mockInstance->validate(function($v){return is_file($v);}, 0);');
		$this->assertEquals('$exText', $t);

		$t = $m->parseVariable('MockValidator::$mockInstance->validate(function($v){return is_file($v);}, 0);');
		$this->assertEquals(false, $t);
	}

	public function testValidCase()
	{
		$file = validate('is_file', __FILE__);
		$this->assertEquals($file, __FILE__);

		$file = validate(['is_string', 'is_file'], __FILE__);
		$this->assertEquals($file, __FILE__);

		$file = validate([function($v){
			return is_string($v);
		},function($v){
			return is_file($v);
		}], __FILE__);
		$this->assertEquals($file, __FILE__);

		$file = validate(function($v){
			return is_string($v) && is_file($v);
		}, __FILE__);
		$this->assertEquals($file, __FILE__);

		$value = validate(function(){
			return true;
		});
		$this->assertEquals(true, $value);

		$value = validate(function(){
			return 0;
		});
		$this->assertEquals(0, $value);
	}

	public function testInversionCase()
	{
		$value = validate(['is_bool'], 'hello world');
		$this->assertContains('VALIDATION FAILURE', $value);

		$value = validate(['!is_bool'], 'hello world');
		$this->assertEquals('hello world', $value);

		$value = validate(['!is_bool', 'is_int'], 'hello world');
		$this->assertContains('VALIDATION FAILURE', $value);

		$value = validate(['!is_bool', '!is_int'], 'hello world');
		$this->assertEquals('hello world', $value);
	}

	public function testInvalidCase()
	{
		$value = validate(function(){
			return false;
		});
		$this->assertContains('VALIDATION FAILURE', $value);

		$value = validate(function(){
			return null;
		});
		$this->assertContains('VALIDATION FAILURE', $value);

		$value = validate(function(){
			return '__invalid___';
		});
		$this->assertContains('VALIDATION FAILURE', $value);

		$value = validate(function(){
			return INVALID;
		});
		$this->assertContains('VALIDATION FAILURE', $value);

		$value = validate(function(){
			return '';
		});
		$this->assertContains('VALIDATION FAILURE', $value);

		// -- unused param $v
		$value = validate(function($v){
			return false;
		});
		$this->assertContains('VALIDATION FAILURE', $value);
	}

	public function testExceptionTextFormat()
	{
		$exText = validate('is_file', '1212', 'hello world');
		$this->assertEquals(
$this->lfstring(
'VALIDATION FAILURE
hello world
1212'), $exText
		);

		$exText = validate('is_file', '1212');
		$line = __LINE__ - 1;
		//print_r($exText);
		$this->assertEquals(
$this->lfstring(
'VALIDATION FAILURE
ValidatorTest.php[' . $line . ']
$exText = 1212'), $exText
		);

		$exText = validate(
			function($v)
			{
			return is_file($v);
			}
		, '1212');
		$line = __LINE__ - 1;
		//print_r($exText);
		$this->assertEquals(
$this->lfstring(
'VALIDATION FAILURE
ValidatorTest.php[' . $line . ']
$exText = 1212'), $exText
		);

		$exText = validate(function($v){return is_file($v);}, '1212');
		$line = __LINE__ - 1;
		//print_r($exText);
		$this->assertEquals(
$this->lfstring(
'VALIDATION FAILURE
ValidatorTest.php[' . $line . ']
$exText = 1212'), $exText
		);

		$exText =
		validate(function($v){return is_file($v);}, '1212');
		$line = __LINE__ - 1;
		$this->assertEquals(
$this->lfstring(
'VALIDATION FAILURE
ValidatorTest.php[' . $line . '] validate(function($v){return is_file($v);}, \'1212\');
value = 1212'), $exText
		);

		$exText = MockValidator::$mockInstance->validate(
			function($v){
				return is_file($v);
			}, '1212');
		$line = __LINE__ - 1;
		//print_r($exText);
		$this->assertEquals(
$this->lfstring(
'VALIDATION FAILURE
ValidatorTest.php[' . $line . ']
$exText = 1212'), $exText
		);

	} // end testInvalidCase
}