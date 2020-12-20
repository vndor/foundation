<?php

namespace Pimple\Tests;

use vndor\Foundation\Container;
use Pimple\Tests\Fixtures\PimpleServiceProvider;
use Pimple\Tests\Fixtures\PimpleServiceProviderContainer;
use Pimple\Tests\Fixtures\Service;

class PimpleServiceProviderInterfaceTest extends \PHPUnit_Framework_TestCase
{
	public function testProvider()
	{
		$pimple = new Container();

		$pimple->register( PimpleServiceProvider::class );

		$this->assertEquals('value', $pimple->param);
		$this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $pimple->service);
		$serviceOne = $pimple->createService;
		$serviceTwo = $pimple->createService;

		$this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $serviceOne);
		$this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $serviceTwo);
		$this->assertNotSame($serviceOne, $serviceTwo);
	}

	public function testProviderWithInitialValues()
	{
		$pimple = new Container();

		$pimple->register( PimpleServiceProviderContainer::class, [
			'constructor_param' => 'value000',
			'constructor_service' => function($c) {
				$s = new Service();
				$s->value = $c->param; // param is inside connectTo :) , some recursion
				return $s;
			}
		]);

		$this->assertEquals('value000', $pimple->param);
		$this->assertInstanceOf('Pimple\Tests\Fixtures\Service', $pimple->service);
		$this->assertEquals('value000', $pimple->service->value);
	}
}
