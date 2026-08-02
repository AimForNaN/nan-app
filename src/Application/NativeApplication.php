<?php

namespace NaN\Application;

use NaN\Application\{
	Interfaces\MiddlewareApplicationInterface,
	Interfaces\ServicesApplicationInterface,
};
use NaN\Collections\Middleware\MiddlewareCollection;
use NaN\DI\Container;
use NaN\Http\{
	Message,ServerRequestFactory,
	Streams\OutputStream,
};
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\{
	Message\ResponseInterface as PsrResponseInterface,
	Message\ServerRequestInterface as PsrServerRequestInterface,
	Server\MiddlewareInterface as PsrMiddlewareInterface,
};

readonly class
	NativeApplication
implements
	MiddlewareApplicationInterface,
	ServicesApplicationInterface
{
	use Traits\ApplicationTrait;

	public function __construct(
		public PsrContainerInterface $services = new Container(),
		public PsrMiddlewareInterface $middleware = new MiddlewareCollection(),
	) {
	}

	/**
	 * Exceptions and errors should be handled on a global level
	 *  (e.g. register_shutdown_function, set_error_handler, set_exception_handler, etc).
	 *
	 * @throws \JsonException
	 */
	public function run(): void {
		if ($this->services->has(PsrServerRequestInterface::class)) {
			$req = $this->services->get(PsrServerRequestInterface::class);
		}

		$req ??= new ServerRequestFactory()->createServerRequest('', '', $_SERVER);
		$rsp = $this->handle($req);

		static::sendResponse($rsp);
	}

	public static function sendBody(PsrResponseInterface $rsp): void {
		new OutputStream()->write((string)$rsp->getBody());
	}

	public static function sendHeaders(PsrResponseInterface $rsp): void {
		$version = $rsp->getProtocolVersion();
		$status = $rsp->getStatusCode();
		$phrase = $rsp->getReasonPhrase();
		\header("HTTP/{$version} {$status} {$phrase}");

		$headers = $rsp->getHeaders();

		foreach ($headers as $name => $value) {
			$value = Message::mergeHeaderValue($value);
			\header("{$name}: {$value}");
		}
	}

	public static function sendResponse(PsrResponseInterface $rsp): void {
		static::sendHeaders($rsp);

		if ($rsp->getStatusCode() !== 204) {
			static::sendBody($rsp);
		}
	}

	public function withMiddleware(PsrMiddlewareInterface $middleware): MiddlewareApplicationInterface {
		return new self($this->services, $middleware);
	}

	public function withServices(PsrContainerInterface $services): ServicesApplicationInterface {
		return new self($services, $this->middleware);
	}
}
