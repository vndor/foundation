<?php
require __DIR__ . '/../src/AException.php';

/* terminal output for manual compare

php example-exception2.php
Case0Exception
Case1Exception
file "file1Name" is not exists for template "template1Name"
Case2Exception
INCLUDE file "file2Name" is not exists

*/

if (php_sapi_name() !== 'cli') {
	header("Content-Type: text/plain");
}

class MyExceptions extends aphp\Foundation\AException 
{	
	
}

class Case0Exception extends MyExceptions { } // empty exception

class Case1Exception extends MyExceptions // 2 param
{ 
	static function someCase($file, $template) {
		return self::textf('file "%s" is not exists for template "%s"', $file, $template);
	}
}

class Case2Exception extends MyExceptions // 1 param
{
	static function includeFile($file) {
		return self::textf('INCLUDE file "%s" is not exists', $file);
	}
}

try {
	throw MyExceptions::createEx('Case0Exception::textf');
} catch (Case0Exception $e) {
	echo 'Case0Exception' . PHP_EOL;
}

try {
	throw MyExceptions::createEx('Case1Exception::someCase', 'file1Name', 'template1Name');
} catch (Case1Exception $e) {
	echo 'Case1Exception' . PHP_EOL;
	echo $e->getMessage() . PHP_EOL;
}

try {
	throw MyExceptions::createEx('Case2Exception::includeFile', 'file2Name');
} catch (Case2Exception $e) {
	echo 'Case2Exception' . PHP_EOL;
	echo $e->getMessage() . PHP_EOL;
}