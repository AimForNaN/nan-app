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
		public Router\RoutesCollection $routes,
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
		$route = $this->routes->match($request->getUri()->getPath());

		if (!$route) {
			return $handler->handle($request);
		}

		return $route->handle($request);
	}
}
