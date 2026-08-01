<?php

namespace NaN\Application\Middleware\Router;

use NaN\Application\Middleware\MiddlewareCollection;
use NaN\Http\{
	ResponseFactory,
	ServerRequest,
};
use Psr\Container\{
	ContainerExceptionInterface as PsrContainerExceptionInterface,
	NotFoundExceptionInterface as PsrNotFoundExceptionInterface,
};
use Psr\Http\{
	Message\ResponseFactoryInterface as PsrResponseFactoryInterface,
	Message\ResponseInterface as PsrResponseInterface,
	Message\ServerRequestInterface as PsrServerRequestInterface,
	Server\MiddlewareInterface as PsrMiddlewareInterface,
	Server\RequestHandlerInterface as PsrRequestHandlerInterface,
};

readonly class Route implements PsrMiddlewareInterface {
	public function __construct(
		public string $path,
		public \Closure|string|null $handler = null,
		public ?string $name = null,
		public ?PsrMiddlewareInterface $middleware = null,
	) {
		if (empty($this->path)) {
			throw new \InvalidArgumentException('Path cannot be empty!');
		}
	}

	public function matches(string $path): bool {
		if ($path === $this->path) {
			return true;
		}

		$pattern = new RoutePattern($this->path);
		$pattern->compile();
		return $pattern->matches($path);
	}

	public function matchesRequest(PsrServerRequestInterface $request): bool {
		return $this->matches($request->getUri()->getPath());
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
		if (\is_null($this->handler)) {
			$response_factory = ServerRequest::getServiceFromRequest(
				PsrResponseFactoryInterface::class,
				$request,
				ResponseFactory::class,
			);

			return $response_factory->createResponse(501);
		}

		$middleware = [];

		if ($this->middleware instanceof PsrMiddlewareInterface) {
			$middleware[] = $this->middleware;
		}

		$middleware[] = new RouteHandler($this);

		$middleware = new MiddlewareCollection(...$middleware);

		return $middleware->process($request, $handler);
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
