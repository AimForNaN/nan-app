<?php

namespace NaN\Application\Middleware\Router;

use NaN\DI\{
	Arguments,
	DelegatesContainer,
};
use Psr\Container\{
	ContainerExceptionInterface as PsrContainerExceptionInterface,
	ContainerInterface as PsrContainerInterface,
	NotFoundExceptionInterface as PsrNotFoundExceptionInterface,
};
use Psr\Http\{
	Message\ResponseInterface as PsrResponseInterface,
	Message\ServerRequestInterface as PsrServerRequestInterface,
	Server\MiddlewareInterface as PsrMiddlewareInterface,
	Server\RequestHandlerInterface as PsrRequestHandlerInterface,
};

readonly class RouteHandler implements PsrMiddlewareInterface {
	public function __construct(
		private \Closure $__callback,
	) {
	}

	/**
	 * @throws PsrContainerExceptionInterface
	 * @throws PsrNotFoundExceptionInterface
	 * @throws \ReflectionException
	 */
	public function process(
		PsrServerRequestInterface $request,
		PsrRequestHandlerInterface $handler,
	): PsrResponseInterface {
		$callback = $this->__callback;
		$delegates = [];

		if ($services = $request->getAttribute(PsrContainerInterface::class)) {
			$delegates[] = $services;
		}

		$container = new DelegatesContainer([
			PsrRequestHandlerInterface::class => $handler,
			PsrServerRequestInterface::class => $request->withoutAttribute(PsrContainerInterface::class),
		])->withDelegates(...$delegates);

		$args = Arguments::fromCallable($callback);
		$resolved = $args->resolve($request->getQueryParams(), $container);

		return $callback(...$resolved);
	}
}
