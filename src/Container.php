<?php
namespace aphp\Foundation;

abstract class ContainerH
{
	/* public function __construct($values = array()) */

	/*Psr\Container\ContainerInterface*/ abstract public function get($id);
	/*Psr\Container\ContainerInterface*/ abstract public function has($id);

	abstract public function raw($id);
	abstract public function keys();

	/*ArrayAccess*/ abstract public function offsetSet($id, $value);
	/*ArrayAccess*/ abstract public function offsetGet($id);
	/*ArrayAccess*/ abstract public function offsetExists($id);
	/*ArrayAccess*/ abstract public function offsetUnset($id);

// Extended api
	abstract public function resetToRaw($id);
	abstract public function resetToRawAll();
	abstract public function unfreeze($id);
	abstract public function freeze($id);

// Magic api
	//public function __set ( $id , $value );
	//public function __get ( $id );
	//public function __isset ( $id );
	//public function __unset ( $id );
}

class Container extends ContainerH implements \ArrayAccess, ContainerProviderInterface {
	use TraitContainer;
	protected $__injectedClasses = [];

	 /**
	 * Instantiates the container.
	 *
	 * Objects and parameters can be passed as argument to the constructor.
	 *
	 * @param array $values The parameters or objects
	 */
	public function __construct($values = array())
	{
		$this->initContainerTrait($values);
	}

// ------------------------------------
// ContainerProviderInterface

	/* bool */ public function register(/*string*/ $providerClass, $providerValues = [])
	{
		if (in_array($providerClass, $this->__injectedClasses)) {
			return false;
		}
		$provider = new $providerClass($providerValues);
		$provider->connectTo($this);
		$this->__injectedClasses[] = $providerClass;
		return true;
	}

	public function connectTo(/*Container*/ $container) {
		// -- override
		// $container->value = 'hello world';
		// $container->someParam = function($c) { return $c->value; };
	}
}
