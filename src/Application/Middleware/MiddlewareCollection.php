<?php

namespace NaN\Application\Middleware;

use Psr\Http\Message\{
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};
use Psr\Http\Server\{
	MiddlewareInterface as PsrMiddlewareInterface,
	RequestHandlerInterface as PsrRequestHandlerInterface,
};

class MiddlewareCollection
implements
	PsrMiddlewareInterface,
	PsrRequestHandlerInterface
{
	protected iterable $_handlers = [];

	public function __construct(
		PsrMiddlewareInterface|PsrRequestHandlerInterface ...$middleware,
	) {
		$this->_handlers = $middleware;
	}
	public function handle(PsrServerRequestInterface $request): PsrResponseInterface {
		$current = \reset($this->_handlers);

		if ($current instanceof PsrRequestHandlerInterface) {
			return $current->handle($request);
		}

		return $current->process($request, $this->withoutMiddleware($current));
	}

	public function process(
		PsrServerRequestInterface $request,
		PsrRequestHandlerInterface $handler,
	): PsrResponseInterface {
		$middleware = [...$this->_handlers, $handler];

		return new static(...$middleware)->handle($request);
	}

	public function withoutMiddleware(
		PsrMiddlewareInterface $middleware
	): PsrMiddlewareInterface|PsrRequestHandlerInterface {
		return new static(
			...\array_filter($this->_handlers, fn($child) => $middleware !== $child),
		);
	}
}
