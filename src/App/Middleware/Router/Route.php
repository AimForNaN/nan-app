<?php

namespace NaN\App\Middleware\Router;

use NaN\App\Controller\Interfaces\ControllerInterface;
use NaN\DI\{
	Arguments,
	Container,
};
use NaN\Http\Response;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\{
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};
use Psr\Http\Server\RequestHandlerInterface as PsrRequestHandlerInterface;

class Route implements \ArrayAccess, \IteratorAggregate, PsrRequestHandlerInterface {
	protected readonly \Closure|string|null $_handler;

	public function __construct(
		public readonly ?string $path = null,
		callable|string|null $handler = null,
		protected array $_children = [],
	) {
		if (!$handler) {
			$handler = function (): PsrResponseInterface {
				return new Response(404);
			};
		}

		$this->_handler = $handler;
	}

	public function contains(Route $route): bool {
		foreach ($this as $child) {
			if ($child === $route) {
				return true;
			}
		}

		return false;
	}

	public function getIterator(): \Traversable {
		yield $this;

		foreach ($this->_children as $route) {
			yield from $route->getIterator();
		}
	}

	/**
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

	public function insert(string $part, Route $route): self {
		$this->_children[$part] = $route;
		return $this;
	}

	public function isValid(): bool {
		return \is_callable($this->_handler) || \is_a($this->_handler, ControllerInterface::class);
	}

	public function match(string $part): ?Route {
		if (isset($this->_children[$part])) {
			return $this->_children[$part];
		}

		foreach ($this->_children as $path => $child) {
			// Skip what's already been tested for!
			if ($path === $part) {
				continue;
			}

			$pattern = new RoutePattern($path);
			$pattern->compile();

			if ($pattern->matches($part)) {
				return $child;
			}
		}

		return null;
	}

	public function matches(string $path): bool {
		$pattern = new RoutePattern($this->path);
		$pattern->compile();
		return $pattern->matches($path);
	}

	public function matchesRequest(PsrServerRequestInterface $request): bool {
		return $this->matches($request->getUri()->getPath());
	}

	public function offsetExists(mixed $offset): bool {
		return isset($this->_children[$offset]);
	}

	public function offsetGet(mixed $offset): mixed {
		return $this->_children[$offset] ?? null;
	}

	public function offsetSet(mixed $offset, mixed $value): void {
		$this->insert($offset, $value);
	}

	public function offsetUnset(mixed $offset): void {
		unset($this->_children[$offset]);
	}

	public function toCallable(PsrServerRequestInterface $request): callable {
		$handler = $this->_handler;

		if (\is_subclass_of($handler, ControllerInterface::class))  {
			$handler = new $handler();
			$allowed_methods = $handler->getAllowedMethods();
			$method = $request->getMethod();

			if ($allowed_methods[$method] ?? false) {
				$method = \strtolower($method);
				return $handler->$method(...);
			}

			return function (): PsrResponseInterface {
				return new Response(405);
			};
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
		return new self($this->path, $handler, $this->_children);
	}

	public function withPath(string $path): static {
		$route = new self($path, $this->_handler, $this->_children);
		return $route;
	}
}
