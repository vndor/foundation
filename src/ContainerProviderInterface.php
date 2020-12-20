<?php
namespace aphp\Foundation;

interface ContainerProviderInterface
{
	// function __construct($values = []);

	function connectTo(/*Container*/ $container);
	//{
	//    -- override
	//    $container->value = 'hello world';
	//    $container->someParam = function($c) { return $c->value; };
	//}
}