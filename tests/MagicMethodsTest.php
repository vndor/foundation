<?php
namespace Pimple\Tests;
use aphp\Foundation\Container;

class MagicMethodsTest extends \PHPUnit_Framework_TestCase {

	// magic methods

	public function testWithString()
	{
		$pimple = new Container();
		$pimple->param = 'value';

		$this->assertEquals('value', $pimple->param);
	}

	public function testWithClosure()
	{
		$pimple = new Container();
		$pimple->service = function () {
			return new Fixtures\Service();
		};

		$this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $pimple->service);
	}

	public function testIsset()
	{
		$pimple = new Container();
		$pimple->param = 'value';
		$pimple->service = function () {
			return new Fixtures\Service();
		};

		$pimple->nullv = null;

		$this->assertTrue(isset($pimple->param));
		$this->assertTrue(isset($pimple->service));
		$this->assertTrue(isset($pimple->nullv));
		$this->assertFalse(isset($pimple->non_existent));

		unset($pimple->param);
		$this->assertFalse(isset($pimple->param));
	}

	// reset to raw

	public function testResetToRaw()
	{
		$pimple = new Container();
		$pimple->__config_useFrozen = false;

		$pimple->i = 0;
		$pimple->incValue = function($c) {
			$c->i++;
			return $c->i;
		};

		$inc1 = $pimple->incValue;
		$inc2 = $pimple->incValue;

		$this->assertTrue($inc1 == 1);
		$this->assertTrue($inc2 == 1);

		$pimple->resetToRaw('incValue');

		$inc1 = $pimple->incValue;
		$inc2 = $pimple->incValue;

		$this->assertTrue($inc1 == 2);
		$this->assertTrue($inc2 == 2);

		$pimple->resetToRawAll();

		$this->assertTrue($pimple->i == 0);

		$inc1 = $pimple->incValue;
		$inc2 = $pimple->incValue;

		$this->assertTrue($pimple->i == 1);
		$this->assertTrue($inc1 == 1);
		$this->assertTrue($inc2 == 1);
	}

	// unfreeze

	/**
	 * @expectedException \aphp\Foundation\FrozenServiceException
	 */
	public function testUnfreezeExeption()
	{
		$pimple = new Container();
		//$pimple->__config_useFrozen = true; // by default

		$pimple->i = 0;
		$this->assertTrue($pimple->i == 0);

		$pimple->i = 2; // throws
	}

	/**
	 * @expectedException \aphp\Foundation\FrozenServiceException
	 */
	public function testUnfreeze()
	{
		$pimple = new Container();
		//$pimple->__config_useFrozen = true; // by default

		$pimple->i = 0;
		$this->assertTrue($pimple->i == 0);

		$pimple->unfreeze('i');
		$pimple->i = 2; // ok
		$this->assertTrue($pimple->i == 2);
		$pimple->i = 3; // ok
		$this->assertTrue($pimple->i == 3);

		$pimple->freeze('i');
		$pimple->i = 4; // throws
	}
}