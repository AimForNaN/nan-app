<?php

namespace NaN\App\Middleware\Router;

use NaN\App\Controller\Interfaces\ControllerInterface;
use NaN\DI\{
	Arguments,
	Container,
};
use NaN\Http\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\{
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};
use Psr\Http\Server\{
	MiddlewareInterface as PsrMiddlewareInterface,
	RequestHandlerInterface as PsrRequestHandlerInterface,
};

readonly class Route implements PsrMiddlewareInterface, PsrRequestHandlerInterface {
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

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 * @throws \ReflectionException
	 */
	public function handle(PsrServerRequestInterface $request): PsrResponseInterface {
		$pattern = new RoutePattern($this->path);
		$pattern->compile();
		$pattern->matchesRequest($request);

		$values = $pattern->getMatches();
		$handler = $this->toCallable($request);
		$delegates = [];

		if ($services = $request->getAttribute(PsrContainerInterface::class)) {
			$delegates[] = $services;
		}

		$container = new Container([
			PsrServerRequestInterface::class => $request,
		], $delegates);

		$args = Arguments::fromCallable($handler);
		$values = $args->resolve($values, $container);

		return $handler(...$values);
	}

	public function isNull(): bool {
		return \is_null($this->handler);
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
	 * @throws ContainerExceptionInterface
	 * @throws \ReflectionException
	 * @throws NotFoundExceptionInterface
	 */
	public function process(
		PsrServerRequestInterface $request,
		PsrRequestHandlerInterface $handler,
	): PsrResponseInterface {
		if ($this->middleware) {
			return $this->middleware->process($request, $this);
		}

		return $this->handle($request);
	}

	public function toCallable(PsrServerRequestInterface $request): callable {
		$handler = $this->handler;

		if ($this->isNull()) {
			return function (): PsrResponseInterface {
				return new Response(501);
			};
		}

		if (\is_subclass_of($handler, ControllerInterface::class))  {
			$handler = new $handler();
			$allowed_methods = $handler->getAllowedMethods();
			$method = $request->getMethod();

			if (isset($allowed_methods[$method])) {
				$method = \strtolower($method);
				return $handler->$method(...);
			}

			return fn(): PsrResponseInterface => new Response(405, [
				'Allow' => \implode(', ', $allowed_methods),
			]);
		}

		return \Closure::bind($handler, $this);
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
