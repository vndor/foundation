<?php
namespace aphp\Foundation;

// see
// 	example/example-validation.php

if (!defined('INVALID')) {
	define('INVALID', '__invalid___');
}

class Validator
{
	use TraitSingleton;

	public $__exceptionClass = AException::class;

	public function validate($validations, $value = null, $exceptionText = null, $traceLevel = 0)
	{
		if (!is_array($validations)) {
			$validations = [ $validations ];
		}
		foreach ($validations as $i => $validation) {
			if ($validation instanceof \Closure) {
				// closure
				$closure = new \ReflectionFunction($validation);
				if ($closure->getNumberOfParameters() == 0) {
					$result = call_user_func($validation);
					$value = $result;
				}
			}
			if (!isset($result)) {
				if (is_string($validation) && $validation[0] == '!') {
					// string
					$result = !call_user_func_array(trim(substr($validation, 1)), [$value]);
				} else {
					// string or closure
					$result = call_user_func_array($validation, [$value]);
				}
			}
			if (!$this->isValid($result)){
				break;
			}
			if ($i < count($validations) - 1) {
				unset($result);
			}
		}
		if (!$this->isValid($result)) {
			$class = $this->__exceptionClass;
			if ($exceptionText) {
				throw new $class(
					sprintf(
						"VALIDATION FAILURE\n%s\n%s",
						$exceptionText,
						print_r($value, true)
					)
				);
			} else {
				$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				throw new $class(
					sprintf(
						"VALIDATION FAILURE\n%s\n%s = %s",
						$this->getExceptionText($trace[$traceLevel]),
						empty($this->lastVariableName) ? 'value' : $this->lastVariableName,
						print_r($value, true)
					)
				);
			}
		}
		return $value;
	}

	protected function isValid($value) {
		if (is_bool($value)) {
			return $value;
		}
		if (is_string($value)) {
			return !empty($value) && $value !== '__invalid___';
		}
		return $value !== null;
	}

	protected $lastVariableName = '';
	protected function getExceptionText($trace)
	{
		$this->lastVariableName = '';
		$file = file($trace['file']);
		$line = $trace['line'];
		$lineText = '';
		while (!$this->parseValidator($lineText) && $line>0) {
			$line--;
			$lineText = $file[ $line ];
		}
		$variable = $this->parseVariable($lineText);
		if ($variable) {
			$this->lastVariableName = $variable;
			return sprintf('%s[%d]', basename($trace['file']), $trace['line']);
		}
		return sprintf('%s[%d] %s', basename($trace['file']), $trace['line'],  trim($lineText));
	}

	protected function parseValidator($text)
	{
		return (strpos($text, 'validate(') !== false);
	}

	protected function parseVariable($text)
	{
		// \Static\Class::$value
		if (preg_match('~\S+::\$\w+~', $text, $matches)) {
			$m = trim($matches[0]);
			if ((strpos($text, '=') === false) || (strpos($text, '=') < strpos($text, $m))) {
				// ___ = \Static\Class::$value
				// not return
			} else {
				return $m;
			}
		}

		// $obj->value
		// $obj->value->getValue()->value
		if (preg_match('~(\$\w+[^=;\?]+?)[\?=]~', $text, $matches)) return trim($matches[1]);
		return false;
	}
}