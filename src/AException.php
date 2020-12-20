<?php
namespace aphp\Foundation;

// see
// 	example/example-exception.php
// 	example/example-exception2.php

class AException extends \RuntimeException
{
	static function create( /* ... */ )
	{
		$args = func_get_args();
		// --
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$trace = $trace[1];
		// --
		$text = $trace['class'] . '::' . $trace['function'] . ' "' . implode('", "', $args) . '"';
		return new static($text);
	}

	static function createEx( /* ... */ )
	{
		$className = \get_called_class();
		$refl = new \ReflectionClass($className);
		$namespace = $refl->getNamespaceName();
		if (!empty($namespace)) {
			$namespace = '\\' . $namespace . '\\';
		}
		$args = func_get_args();
		$a = explode('::', $args[0]);
		$class = $namespace . $a[0];
		$method = $a[1];
		unset($args[0]);
		return $class::$method(...$args);
	}

	static function textf( /* ... */ )
	{
		$args = func_get_args();
		if (count($args) == 0) {
			return new static('');
		}
		$text = $args[0];
		unset($args[0]);
		return new static(sprintf($text, ...$args));
	}
}