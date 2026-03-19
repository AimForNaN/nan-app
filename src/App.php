<?php

namespace NaN;

use NaN\App\Middleware\Traits\MiddlewareIteratorTrait;
use NaN\DI\Container;
use NaN\Http\{Request,Response};
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\{
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};
use Psr\Http\Server\{
	MiddlewareInterface as PsrMiddlewareInterface,
	RequestHandlerInterface as PsrRequestHandlerInterface,
};

class App implements \Iterator, PsrRequestHandlerInterface {
	use MiddlewareIteratorTrait;

	public function __construct(
		public readonly PsrContainerInterface $services = new Container(),
		iterable $middleware = [],
	) {
		$this->_middleware = $middleware;
	}

	public function handle(PsrServerRequestInterface $request): PsrResponseInterface {
		if (!$this->valid()) {
			return new Response(404);
		}

		$current = $this->current();
		$this->next();

		return $current->process($request, $this);
	}

	/**
	 * Exceptions and errors should be handled on a global level
	 *  (e.g. register_shutdown_function, set_error_handler, set_exception_handler, etc).
	 */
	public function run(): void {
		$this->rewind();

		$req = Request::fromGlobals()
			->withAttribute(PsrContainerInterface::class, $this->services)
		;
		$rsp = $this->handle($req);

		Response::send($rsp);
	}

	public function withMiddleware(PsrMiddlewareInterface ...$middleware): static {
		$new = clone $this;
		$new->_middleware = $middleware;
		return $new;
	}

	public function withServices(PsrContainerInterface $container): static {
		return new self($container, $this->_middleware);
	}
}
