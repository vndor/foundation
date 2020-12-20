<?php
require __DIR__ . '/../src/AException.php';

/* terminal output for manual compare

php example05.php
helloWorld
f2
MyException::dirIsNotExists "dirValue"
MyException::dirIsNotExists "alone"
MyException::problem2params "problem1", "problem2"

*/

if (php_sapi_name() !== 'cli') {
	header("Content-Type: text/plain");
}

class MyException extends vndor\Foundation\AException 
{	
	static function dirIsNotExists($dir)
	{
		return self::create($dir);
	}
	static function problem2params($param1, $param2)
	{
		return self::create($param1, $param2);
	}
}

class A {
	function helloWorld() {
		echo 'helloWorld' . PHP_EOL;
		$this->f2();
	}
	function f2() {
		echo 'f2' . PHP_EOL;
		throw MyException::dirIsNotExists('dirValue');
	}
}

try {
	$a = new A;
	$a->helloWorld();
} catch (Exception $e) {
	echo $e->getMessage() . PHP_EOL;
}

try {
	throw MyException::dirIsNotExists('alone');
} catch (Exception $e) {
	echo $e->getMessage() . PHP_EOL;
}

try {
	throw MyException::problem2params('problem1', 'problem2');
} catch (Exception $e) {
	echo $e->getMessage() . PHP_EOL;
}