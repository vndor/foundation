<?php

namespace Pimple\Tests\Fixtures;

use aphp\Foundation\Container;
use aphp\Foundation\ContainerProviderInterface;

class PimpleServiceProvider implements ContainerProviderInterface
{
	function connectTo(/*Container*/ $container) {
		$container->param = 'value';

		$container->service = function () {
			return new Service();
		};

		$container->createService = $container->factory(function () {
			return new Service();
		});
	}
}

class PimpleServiceProviderContainer extends Container
{
	function connectTo(/*Container*/ $container) {
		// constructor_param and constructor_service passed in Container::__construct
		$container->param = $this->raw('constructor_param');
		$container->service = $this->raw('constructor_service');
	}
}
