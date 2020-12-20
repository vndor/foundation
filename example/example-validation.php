<?php

require '../vendor/autoload.php';

/* terminal output for manual compare

php example-validation.php
VALIDATION FAILURE
example-validation.php[117]
$file = invalid/path
VALIDATION FAILURE
custom exception text
invalid/path
VALIDATION FAILURE
example-validation.php[121]
Foo::$value = invalid/path
VALIDATION FAILURE
example-validation.php[123]
$foo->file = invalid/path
VALIDATION FAILURE
example-validation.php[93]
$this->file = invalid/path
VALIDATION FAILURE
example-validation.php[97]
$this->file->name = invalid/path
VALIDATION FAILURE
example-validation.php[103]
$this->file->getValue()->dd = __invalid___
VALIDATION FAILURE
example-validation.php[106]
$this->file = invalid
VALIDATION FAILURE
example-validation.php[132]
$a = invalid/path
VALIDATION FAILURE
example-validation.php[136]
$b = invalid/path
VALIDATION FAILURE
example-validation.php[141]
$c = invalid/path
VALUE example-validation.php
VALID $file = validate('is_file', __FILE__);
VALUE example-validation.php
VALID $foo->file = validate("is_file", __FILE__);
VALUE valid
VALID $foo->fileExec4valid();
VALUE validFile
VALID $file = validate(function() { return "validFile"; });
VALIDATION FAILURE
example-validation.php[162]
$file = stdClass Object
(
)

*/
if (php_sapi_name() !== 'cli') {
	header("Content-Type: text/plain");
}

use function vndor\Foundation\validate;
use vndor\Foundation\AException as VE;

class Foo {
	public $file;
	static $value;

	public function fileExec() {
		$this->file = validate('is_file', 'invalid/path');
	}
	public function fileExec2() {
		$this->file = new vndor\Foundation\ANullObject;
		$this->file->name = validate('is_file', 'invalid/path');
	}
	public function fileExec3() {
		$this->file = new vndor\Foundation\ANullObject;
		$this->file->getValue()->dd = validate(function(){
			return INVALID;
		});
	}
	public function fileExec4() {
		$this->file = validate(function($a){ return $a == 'valid'; }, 'invalid');
	}
	public function fileExec4valid() {
		$this->file = validate(function($a){ return $a == 'valid'; }, 'valid');
	}
}

//
function echoVE($e) { echo $e->getMessage().PHP_EOL; }
$foo = new Foo();

try { $file = validate('is_file', 'invalid/path'); } catch (VE $e) { echoVE($e); }

try { $file = validate('is_file', 'invalid/path', 'custom exception text'); } catch (VE $e) { echoVE($e); }

try { Foo::$value = validate(	'is_file', 'invalid/path' ); } catch (VE $e) {echoVE($e); }

try { $foo->file = validate( 'is_file', 'invalid/path'); } catch (VE $e) { echoVE($e); }

try { $foo->fileExec(); } catch (VE $e) { echoVE($e); }
try { $foo->fileExec2(); } catch (VE $e) { echoVE($e); }
try { $foo->fileExec3(); } catch (VE $e) { echoVE($e); }
try { $foo->fileExec4(); } catch (VE $e) { echoVE($e); }

// multiple

try { $a = validate(['is_string', 'is_file'], 'invalid/path'); } catch (VE $e) { echoVE($e); }
try { $b = validate(
	['is_string',
	'is_file'
], 'invalid/path'); } catch (VE $e) { echoVE($e); }

try { $c = validate(
	[function($a) { return is_string($a); },
	function($a) { return is_file($a); }
], 'invalid/path'); } catch (VE $e) { echoVE($e); }

// valid

$file = validate('is_file', __FILE__);
echo 'VALUE ' . basename($file) . PHP_EOL;
echo 'VALID ' . '$file = validate(\'is_file\', __FILE__);' . PHP_EOL;

$foo = new Foo();
$foo->file = validate("is_file", __FILE__);
echo 'VALUE ' . basename($foo->file) . PHP_EOL;
echo 'VALID ' . '$foo->file = validate("is_file", __FILE__);' . PHP_EOL;

$foo->fileExec4valid();
echo 'VALUE ' . $foo->file . PHP_EOL;
echo 'VALID ' . '$foo->fileExec4valid();' . PHP_EOL;

$file = validate(function() { return "validFile"; });
echo 'VALUE ' . $file . PHP_EOL;
echo 'VALID ' . '$file = validate(function() { return "validFile"; });' . PHP_EOL;

try { $file = validate(['is_string','file_exists'], new StdClass); } catch (VE $e) { echoVE($e); }