<?php

namespace NaN\App\Middleware;

use NaN\App\Middleware\Router\Route;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\{
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};
use Psr\Http\Server\{
	MiddlewareInterface as PsrMiddlewareInterface,
	RequestHandlerInterface as PsrRequestHandlerInterface,
};

class Router implements \ArrayAccess, \IteratorAggregate, PsrMiddlewareInterface {
	protected array $_named_routes = [];

	public function __construct(
		protected(set) Route $root = new Route('/'),
	) {
	}

	public function contains(Route $route): bool {
		if ($this->root === $route) {
			return true;
		}

		return $this->root->contains($route);
	}

	public function getIterator(): \Traversable {
		yield from $this->root->getIterator();
	}

	public function insert(string $path, mixed $handler, ?string $name = null): Route {
		if ($path === '/') {
			$this->root = new Route('/', $handler);
		}

		$parts = $this->parsePath($path);
		$current = $this->root;
		$route_path = rtrim($current->path, '/');

		foreach ($parts as $part) {
			$route_path .= '/' . $part;

			if (!isset($current[$part])) {
				$route = new Route($route_path, $handler);
				$current[$part] = $route;
			}

			$current = $current[$part];
		}

		if ($name) {
			$this->_named_routes[$name] = $current;
		}

		return $current;
	}

	public function insertRoute(Route $route, ?string $name = null): Route {
		if ($route->path === '/') {
			$this->root = $route;
		}

		$parts = $this->parsePath($route->path);
		$current = $this->root;

		foreach ($parts as $part) {
			if (!isset($current[$part])) {
				$current[$part] = $route;
			}

			$current = $current[$part];
		}

		if ($name) {
			$this->_named_routes[$name] = $current;
		}

		return $current;
	}

	public function match(string $path): ?Route {
		$parts = $this->parsePath($path);

		$parent = $this->root;
		foreach ($parts as $part) {
			$match = $parent->match($part);

			if (!$match) {
				return null;
			}

			$parent = $match;
		}

		return $parent;
	}

	public function matchName(string $name): ?Route {
		return $this->_named_routes[$name] ?? null;
	}

	public function offsetExists(mixed $offset): bool {
		return (bool)$this->match($offset);
	}

	public function offsetGet(mixed $offset): ?Route {
		return $this->match($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void {
		$this->insert($offset, $value);
	}

	public function offsetUnset(mixed $offset): void {
		throw new \BadMethodCallException('Cannot unset route!');
	}

	protected function parsePath(string $path): array {
		return \array_filter(\explode('/', ltrim($path, '/')));
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 * @throws \ReflectionException
	 */
	public function process(
		PsrServerRequestInterface $request,
		PsrRequestHandlerInterface $handler,
	): PsrResponseInterface {
		$route = $this->match($request->getUri()->getPath());

		if (!$route) {
			return $handler->handle($request);
		}

		return $route->handle($request);
	}

	public function setName(string $name, Route $route): static {
		$this->_named_routes[$name] = $route;

		return $this;
	}
}
