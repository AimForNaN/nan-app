<?php

namespace NaN\Application;

use NaN\Application\{
	Interfaces\MiddlewareApplicationInterface,
	Interfaces\ServicesApplicationInterface,
};
use NaN\Collections\Middleware\MiddlewareCollection;
use NaN\DI\Container;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\{
	Message\ServerRequestInterface,
	Server\MiddlewareInterface as PsrMiddlewareInterface,
};

readonly class ReactApplication
implements MiddlewareApplicationInterface, ServicesApplicationInterface {
	use Traits\ApplicationTrait;

	public function __construct(
		public string $address,
		public array $options = [],
		public PsrContainerInterface $services = new Container(),
		public PsrMiddlewareInterface $middleware = new MiddlewareCollection(),
	) {
	}

	public function run(): void {
		$server = new \React\Http\HttpServer(function (ServerRequestInterface $request) {
			return $this->handle($request);
		});
		$socket = new \React\Socket\SocketServer($this->address, $this->options);

		$server->listen($socket);
	}

	public function withMiddleware(PsrMiddlewareInterface $middleware): MiddlewareApplicationInterface {
		return new self(
			$this->address,
			$this->options,
			$this->services,
			$middleware,
		);
	}

	public function withServices(PsrContainerInterface $services): ServicesApplicationInterface {
		return new self(
			$this->address,
			$this->options,
			$services,
			$this->middleware,
		);
	}
}
