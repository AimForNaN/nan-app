<?php

namespace NaN\App\Middleware\Router;

use NaN\Collections\Collection;
use Psr\Container\{
	ContainerExceptionInterface,
	NotFoundExceptionInterface,
};
use Psr\Http\Message\ServerRequestInterface as PsrServerRequestInterface;
use Psr\Http\Server\{
	MiddlewareInterface as PsrMiddlewareInterface,
	RequestHandlerInterface as PsrRequestHandlerInterface,
};

/**
 * @note Probably won't ever do anything about how simple we handle parameterized routes.
 */
class RoutesCollection extends Collection implements PsrMiddlewareInterface {
	protected array $_named_routes = [];

	public function __construct(Route ...$routes) {
		parent::__construct();

		$this->_processRoutes($routes);
	}

	public function contains(?Route $needle): bool {
		if (\is_null($needle)) {
			return false;
		}

		return \iter\any(fn(Route $route) => $needle === $route, $this->getIterator());
	}

	public function count(): int {
		return \iterator_count($this->getIterator());
	}

	public function getIterator(): \Traversable {
		return \iter\flatten($this->_data);
	}

	public function match(string $path): ?Route {
		if ($path === '/') {
			return $this->_data[0] ?? null;
		}

		$parts = $this->_parsePath($path);
		$current = $this->_data;

		foreach ($parts as $part) {
			if (isset($current['_'])) {
				$part = '_';
			}

			if (!isset($current[$part])) {
				return null;
			}

			$current = $current[$part];
		}

		return $current[0] ?? null;
	}

	public function matchName(string $name): ?Route {
		if (empty($name)) {
			return null;
		}

		return $this->_named_routes[$name] ?? null;
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws \ReflectionException
	 * @throws NotFoundExceptionInterface
	 */
	public function process(
		PsrServerRequestInterface $request,
		PsrRequestHandlerInterface $handler,
	): \Psr\Http\Message\ResponseInterface {
		$route = $this->match($request->getUri()->getPath());

		if (!$route) {
			return $handler->handle($request);
		}

		return $route->process($request, $handler);
	}

	protected function _parsePath(string $path): array {
		return \array_filter(\explode('/', ltrim($path, '/')));
	}

	protected function _processRoute(Route $route): void {
		$this->_setNamedRoute($route);

		if ($route->path === '/') {
			$this->_data[0] = $route;
			return;
		}

		$parts = $this->_parsePath($route->path);
		$current = &$this->_data;

		foreach ($parts as $part) {
			if (RoutePattern::checkParameters($part)) {
				$part = '_';
			}

			if (!isset($current[$part])) {
				$current[$part] = [];
			}

			$current = &$current[$part];
		}

		$current[0] = $route;
	}

	protected function _processRoutes(array $routes): void {
		foreach ($routes as $route) {
			$this->_processRoute($route);
		}
	}

	protected function _setNamedRoute(Route $route): void {
		if (empty($route->name)) {
			return;
		}

		$current = $this->_named_routes[$route->name] ?? null;

		if ($current && $current !== $route) {
			\trigger_error("Replacing route named '{$route->name}'!", E_USER_WARNING);
		}

		$this->_named_routes[$route->name] = $route;
	}
}
