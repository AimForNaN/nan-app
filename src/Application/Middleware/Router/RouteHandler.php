<?php

namespace NaN\Application\Middleware\Router;

use NaN\Application\Controller\Interfaces\ControllerInterface;
use NaN\DI\{
	Arguments,
	DelegatesContainer,
};
use NaN\Http\Response;
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
		private Route $__route,
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
		return $this->__handle($request);
	}

	private function __toCallable(PsrServerRequestInterface $request): callable {
		$handler = $this->__route->handler;

		if (\is_subclass_of($handler, ControllerInterface::class))  {
			$handler = new $handler();
			$allowed_methods = $handler->getAllowedMethods();
			$method = $request->getMethod();

			if (isset($allowed_methods[$method])) {
				$method = \strtolower($method);
				return $handler->$method(...);
			}

			return fn(): PsrResponseInterface => new Response(405, '', [
				'Allow' => \implode(', ', $allowed_methods),
			]);
		}

		return \Closure::bind($handler, $this->__route);
	}

	/**
	 * @throws PsrContainerExceptionInterface
	 * @throws PsrNotFoundExceptionInterface
	 * @throws \ReflectionException
	 */
	private function __handle(PsrServerRequestInterface $request): PsrResponseInterface {
		$pattern = new RoutePattern($this->__route->path);
		$pattern->compile();
		$pattern->matchesRequest($request);

		$values = $pattern->getMatches();
		$handler = $this->__toCallable($request);
		$delegates = [];

		if ($services = $request->getAttribute(PsrContainerInterface::class)) {
			$delegates[] = $services;
		}

		$container = new DelegatesContainer([
			PsrServerRequestInterface::class => $request->withoutAttribute(PsrContainerInterface::class),
		])->withDelegates(...$delegates);

		$args = Arguments::fromCallable($handler);
		$values = $args->resolve($values, $container);

		return $handler(...$values);
	}
}
