<?php

use NaN\Http\{
	ResponseFactory,
	ServerRequestFactory,
};
use Psr\Http\{
	Message\ResponseFactoryInterface,
	Message\ServerRequestInterface,
};

return new NaN\DI\Container([
	ResponseFactoryInterface::class => ResponseFactory::class,
	ServerRequestInterface::class => new ServerRequestFactory()->createServerRequest(
		$_SERVER['REQUEST_METHOD'],
		$_SERVER['REQUEST_URI'],
		$_SERVER,
	),
]);
