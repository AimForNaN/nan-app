<?php

namespace NaN\App\Middleware;

use Psr\Container\{
	ContainerExceptionInterface,
	NotFoundExceptionInterface,
};
use Psr\Http\Message\{
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};
use Psr\Http\Server\{
	MiddlewareInterface as PsrMiddlewareInterface,
	RequestHandlerInterface as PsrRequestHandlerInterface,
};

readonly class Router implements PsrMiddlewareInterface {
	public function __construct(
		public PsrMiddlewareInterface $routes,
	) {
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 * @throws \ReflectionException
	 */
	public function process(
		PsrServerRequestInterface $request,
		PsrRequestHandlerInterface $handler,
	): PsrResponseInterface {
		return $this->routes->process($request, $handler);
	}
}
