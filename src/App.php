<?php

namespace NaN;

use NaN\App\Middleware\MiddlewareCollection;
use NaN\DI\Container;
use NaN\Http\{
	Message,
	ResponseFactory,
	ServerRequestFactory,
	Streams\OutputStream,
};
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
		return new ResponseFactory()->createResponse(404);
	}

	/**
	 * Exceptions and errors should be handled on a global level
	 *  (e.g. register_shutdown_function, set_error_handler, set_exception_handler, etc).
	 */
	public function run(?PsrServerRequestInterface $req = null): PsrResponseInterface {
		if (\is_null($req)) {
			$req = new ServerRequestFactory()->createServerRequest(
				$_SERVER['REQUEST_METHOD'],
				$_SERVER['REQUEST_URI'],
				$_SERVER,
			);
		}

		$req = $req->withAttribute(PsrContainerInterface::class, $this->services);
		$rsp = $this->middleware->process($req, $this);

		static::send($rsp);

		return $rsp;
	}

	public static function send(PsrResponseInterface $rsp): void {
		static::sendHeaders($rsp);

		if ($rsp->getStatusCode() !== 204) {
			static::sendBody($rsp);
		}
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

	public function withMiddleware(PsrMiddlewareInterface $middleware): static {
		return new self($this->services, $middleware);
	}

	public function withServices(PsrContainerInterface $container): static {
		return new self($container, $this->middleware);
	}
}
