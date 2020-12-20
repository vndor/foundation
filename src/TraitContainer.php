<?php
namespace vndor\Foundation;

// ------------------------------------
// Exceptions

class UnknownIdentifierException extends \InvalidArgumentException
{
	public function __construct($id)
	{
		parent::__construct(\sprintf('Identifier "%s" is not defined.', $id));
	}
}

class FrozenServiceException extends \RuntimeException
{
	public function __construct($id)
	{
		parent::__construct(\sprintf('Cannot override frozen service "%s".', $id));
	}
}

class InvalidServiceIdentifierException extends \InvalidArgumentException
{
	public function __construct($id)
	{
		parent::__construct(\sprintf('Identifier "%s" does not contain an object definition.', $id));
	}
}

class ExpectedInvokableException extends \InvalidArgumentException {}

// ------------------------------------

trait TraitContainer
{
	protected $__values = [];
	protected $__factories;
	protected $__protected;
	protected $__frozen = [];
	protected $__raw = [];
	protected $__keys = [];
	public $__config_useFrozen = true;

	/**
	 * Instantiates the container.
	 *
	 * Objects and parameters can be passed as argument to the constructor.
	 *
	 * @param array $values The parameters or objects
	 */

	public function initContainerTrait($values = array()) {
		$this->__factories = new \SplObjectStorage();
		$this->__protected = new \SplObjectStorage();

		foreach ($values as $key => $value) {
			$this->offsetSet($key, $value);
		}
	}

	/*ContainerInterface*/ public function get($id) {
		return $this->offsetGet($id);
	}

	/*ContainerInterface*/ public function has($id) {
		return $this->offsetExists($id);
	}

	/*ArrayAccess*/ public function offsetSet($id, $value)
	{
		if (isset($this->__frozen[$id])) {
			throw new FrozenServiceException($id);
		}

		$this->__values[$id] = $value;
		$this->__keys[$id] = true;
	}

	/*ArrayAccess*/ public function offsetGet($id)
	{
		if (!isset($this->__keys[$id])) {
			throw new UnknownIdentifierException($id);
		}

		if (
			isset($this->__raw[$id])
			|| !\is_object($this->__values[$id])
			|| isset($this->__protected[$this->__values[$id]])
			|| !\method_exists($this->__values[$id], '__invoke')
		) {
			if (!isset($this->__raw[$id])) {
				$this->saveValueAndRaw($id, $this->__values[$id], $this->__values[$id]);
			}
			return $this->__values[$id];
		}

		if (isset($this->__factories[$this->__values[$id]])) {
			return $this->closureExec( $this->__values[$id] );
		}

		$raw = $this->__values[$id];
		$val = $this->closureExec( $raw );

		$this->saveValueAndRaw($id, $val, $raw);

		return $val;
	}

	/*ArrayAccess*/ public function offsetExists($id)
	{
		return isset($this->__keys[$id]);
	}

	/*ArrayAccess*/ public function offsetUnset($id)
	{
		if (isset($this->__keys[$id])) {
			if (\is_object($this->__values[$id])) {
				unset($this->__factories[$this->__values[$id]], $this->__protected[$this->__values[$id]]);
			}

			unset($this->__values[$id], $this->__frozen[$id], $this->__raw[$id], $this->__keys[$id]);
		}
	}

	public function raw($id)
	{
		if (!isset($this->__keys[$id])) {
			throw new UnknownIdentifierException($id);
		}
		if (isset($this->__raw[$id])) {
			return $this->__raw[$id];
		}
		return $this->__values[$id];
	}

	public function keys()
	{
		return \array_keys($this->__values);
	}

// ------------------------------------
// Extended api

	public function resetToRaw($id)
	{
		$raw = $this->raw($id);
		$this->__values[$id] = $raw;
		unset(
			$this->__frozen[$id],
			$this->__raw[$id]
		);
	}

	public function resetToRawAll()
	{
		$keys = $this->keys();
		foreach ($keys as $key) {
			$this->resetToRaw($key);
		}
	}

	public function unfreeze($id) {
		if (!isset($this->__keys[$id]))
			throw new UnknownIdentifierException($id);

		unset($this->__frozen[$id]);
	}

	public function freeze($id) {
		if (!isset($this->__keys[$id]))
			throw new UnknownIdentifierException($id);

		$this->__frozen[$id] = true;
	}

// ------------------------------------
// Magic api

	public function __set ( $id , $value ) {
		$this->offsetSet($id, $value);
	}

	public function __get ( $id ) {
		return $this->offsetGet($id);
	}

	public function __isset ( $id ) {
		return $this->offsetExists($id);
	}

	public function __unset ( $id ) {
		$this->offsetUnset($id);
	}

// ------------------------------------
// protected

	protected function closureExec($closure)
	{
		if ($closure instanceof \Closure) {
			$ref = new \ReflectionFunction($closure);
			if ($ref->getNumberOfParameters() == 0) {
				return \call_user_func($closure);
			}
		}
		return \call_user_func_array($closure, [ $this ]);
	}

	protected function saveValueAndRaw($id, $val, $raw)
	{
		if ($this->__config_useFrozen)
			$this->__frozen[$id] = true;

		$this->__values[$id] = $val;
		$this->__raw[$id] = $raw;
	}

// ------------------------------------
// pimple features

	/**
	 * Marks a callable as being a factory service.
	 *
	 * @param callable $callable A service definition to be used as a factory
	 *
	 * @return callable The passed callable
	 *
	 * @throws ExpectedInvokableException Service definition has to be a closure or an invokable object
	 */
	public function factory($callable)
	{
		if (!\method_exists($callable, '__invoke')) {
			throw new ExpectedInvokableException('Service definition is not a Closure or invokable object.');
		}
		$this->__factories->attach($callable);
		return $callable;
	}

	/**
	 * Protects a callable from being interpreted as a service.
	 *
	 * This is useful when you want to store a callable as a parameter.
	 *
	 * @param callable $callable A callable to protect from being evaluated
	 *
	 * @return callable The passed callable
	 *
	 * @throws ExpectedInvokableException Service definition has to be a closure or an invokable object
	 */
	public function protect($callable)
	{
		if (!\method_exists($callable, '__invoke')) {
			throw new ExpectedInvokableException('Callable is not a Closure or invokable object.');
		}
		$this->__protected->attach($callable);
		return $callable;
	}

	/**
	 * Extends an object definition.
	 *
	 * Useful when you want to extend an existing object definition,
	 * without necessarily loading that object.
	 *
	 * @param string   $id       The unique identifier for the object
	 * @param callable $callable A service definition to extend the original
	 *
	 * @return callable The wrapped callable
	 *
	 * @throws UnknownIdentifierException        If the identifier is not defined
	 * @throws FrozenServiceException            If the service is frozen
	 * @throws InvalidServiceIdentifierException If the identifier belongs to a parameter
	 * @throws ExpectedInvokableException        If the extension callable is not a closure or an invokable object
	 */
	public function extend($id, $callable)
	{
		if (!isset($this->__keys[$id])) {
			throw new UnknownIdentifierException($id);
		}

		if (isset($this->__frozen[$id])) {
			throw new FrozenServiceException($id);
		}

		if (!\is_object($this->__values[$id]) || !\method_exists($this->__values[$id], '__invoke')) {
			throw new InvalidServiceIdentifierException($id);
		}

		if (isset($this->__protected[$this->__values[$id]])) {
			throw new InvalidServiceIdentifierException('Container->extend("'.$id.'") , "'.$id.'" is PROTECTED');
		}

		if (!\is_object($callable) || !\method_exists($callable, '__invoke')) {
			throw new ExpectedInvokableException('Extension service definition is not a Closure or invokable object.');
		}

		$factory = $this->__values[$id];

		$extended = function () use ($callable, $factory) {
			$val = $this->closureExec($factory);
			return $callable($val, $this);
		};

		if (isset($this->__factories[$factory])) {
			$this->__factories->detach($factory);
			$this->__factories->attach($extended);
		}

		return $this[$id] = $extended;
	}
}