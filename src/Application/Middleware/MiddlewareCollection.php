<?php

namespace NaN\Application\Middleware;

use NaN\Collections\Collection;
use Psr\Http\{
	Message\ResponseInterface as PsrResponseInterface,
	Message\ServerRequestInterface as PsrServerRequestInterface,
	Server\MiddlewareInterface as PsrMiddlewareInterface,
	Server\RequestHandlerInterface as PsrRequestHandlerInterface,
};

class MiddlewareCollection
extends
	Collection
implements
	PsrMiddlewareInterface,
	PsrRequestHandlerInterface
{
	protected PsrRequestHandlerInterface $_handler;

	public function __construct(
		PsrMiddlewareInterface ...$middleware,
	) {
		parent::__construct(...$middleware);
	}

	public function handle(PsrServerRequestInterface $request): PsrResponseInterface {
		$current = $this->getFirst();

		if (!$current) {
			return $this->_handler->handle($request);
		}

		return $current->process($request, $this->withoutMiddleware($current));
	}

	public function process(
		PsrServerRequestInterface $request,
		PsrRequestHandlerInterface $handler,
	): PsrResponseInterface {
		$this->_handler = $handler;

		return $this->handle($request);
	}

	public function withoutMiddleware(
		PsrMiddlewareInterface $middleware
	): PsrMiddlewareInterface|PsrRequestHandlerInterface {
		return new static(
			...\iter\filter(
				fn($child) => $middleware !== $child,
				$this->getIterator(),
			),
		);
	}
}
