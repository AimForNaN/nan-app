<?php

namespace NaN\Application;

use NaN\Application\Handlers\NotFoundHandler;
use NaN\Application\Middleware\MiddlewareCollection;
use NaN\DI\Container;
use NaN\Http\{
	Message,
	ServerRequestFactory,
	Streams\OutputStream,
};
use Psr\Container\{
	ContainerExceptionInterface,
	ContainerInterface as PsrContainerInterface,
	NotFoundExceptionInterface,
};
use Psr\Http\Message\{
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};
use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;

readonly class NativeApplication implements Interfaces\ApplicationInterface {
	public function __construct(
		public PsrContainerInterface $services = new Container(),
		public PsrMiddlewareInterface $middleware = new MiddlewareCollection(),
	) {
	}

	public function handle(PsrServerRequestInterface $request): PsrResponseInterface {
		$request = $request->withAttribute(PsrContainerInterface::class, $this->services);

		return $this->middleware->process($request, new NotFoundHandler());
	}

	/**
	 * Exceptions and errors should be handled on a global level
	 *  (e.g. register_shutdown_function, set_error_handler, set_exception_handler, etc).
	 *
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerExceptionInterface
	 * @throws \ReflectionException
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

	public function withMiddleware(PsrMiddlewareInterface $middleware): static {
		return new self($this->services, $middleware);
	}

	public function withServices(PsrContainerInterface $container): static {
		return new self($container, $this->middleware);
	}
}
