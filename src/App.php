<?php

namespace NaN;

use NaN\App\Middleware\{
	MiddlewareCollection,
};
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

readonly class App implements PsrRequestHandlerInterface {
	public function __construct(
		public PsrContainerInterface $services = new Container(),
		public PsrMiddlewareInterface $middleware = new MiddlewareCollection(),
	) {
	}

	public function handle(PsrServerRequestInterface $request): PsrResponseInterface {
		return new Response(404);
	}

	/**
	 * Exceptions and errors should be handled on a global level
	 *  (e.g. register_shutdown_function, set_error_handler, set_exception_handler, etc).
	 */
	public function run(?PsrServerRequestInterface $req = null): PsrResponseInterface {
		if (\is_null($req)) {
			$req = Request::fromGlobals();
		}

		$req = $req->withAttribute(PsrContainerInterface::class, $this->services);
		$rsp = $this->middleware->process($req, $this);

		Response::send($rsp);

		return $rsp;
	}

	public function withMiddleware(PsrMiddlewareInterface $middleware): static {
		return new self($this->services, $middleware);
	}

	public function withServices(PsrContainerInterface $container): static {
		return new self($container, $this->middleware);
	}
}
