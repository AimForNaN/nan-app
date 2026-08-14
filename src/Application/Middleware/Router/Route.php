<?php

namespace NaN\Application\Middleware\Router;

use Psr\Http\{
	Message\ResponseInterface as PsrResponseInterface,
	Message\ServerRequestInterface as PsrServerRequestInterface,
	Server\MiddlewareInterface as PsrMiddlewareInterface,
	Server\RequestHandlerInterface as PsrRequestHandlerInterface,
};

readonly class Route implements PsrMiddlewareInterface {
	public function __construct(
		public string $path,
		public PsrMiddlewareInterface $handler,
		public ?string $name = null,
	) {
		if (empty($this->path)) {
			throw new \ValueError('Path cannot be empty!');
		}
	}

	public function process(
		PsrServerRequestInterface $request,
		PsrRequestHandlerInterface $handler,
	): PsrResponseInterface {
		$pattern = new RoutePattern($this->path);

		$pattern->compile();
		$pattern->matches($request->getUri()->getPath());

		$values = $pattern->getMatches();
		$request = $request->withQueryParams($values + $request->getQueryParams());

		return $this->handler->process($request, $handler);
	}

	/**
	 * @todo
	 */
	public function toUrl(...$params): string {
		$pattern = new RoutePattern($this->path);
		$pattern->compile();

		if ($pattern->hasParameters()) {
		}

		return $this->path;
	}

	public function withHandler(mixed $handler): static {
		return new self($this->path, $handler);
	}

	public function withMiddleware(PsrMiddlewareInterface $middleware): static {
		return new self($this->path, $this->handler, null, $middleware);
	}

	public function withPath(string $path): static {
		return new self($path, $this->handler);
	}
}
