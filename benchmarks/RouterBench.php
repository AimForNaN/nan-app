<?php

use NaN\Application\Middleware\Router\{
	Route,
	ClosureMiddleware,
	RoutePattern,
	RoutesCollection,
};
use NaN\Http\{
	Response,
	ServerRequestFactory,
};

if (!\function_exists('getallheaders')) {
	function getallheaders() {
		return [];
	}
}

/**
 * @BeforeMethods({
 *     "benchParamNanRoutesArrayInsert",
 *     "benchParamNanRouterInsertManual",
 *     "benchStaticNanRoutesArrayInsert",
 *     "benchStaticNanRouterInsertManual"
 * })
 */
class RouterBench {
	private array $__param_routes_array = [];
	private array $__static_routes_array = [];
	private RoutesCollection $__param_routes_collection;
	private RoutesCollection $__static_routes_collection;

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchParamNanRoutesArrayInsert(): array {
		$routes = [];

		for ($x = 0; $x < 1000; $x++) {
			$routes[] = new Route('/param/' . $x . '/{id}', new ClosureMiddleware(function ($id) {
				return new Response(200);
			}));
		}

		return $this->__param_routes_array = $routes;
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchParamNanRouterInsertManual(): RoutesCollection {
		$generator = function () {
			for ($x = 0; $x < 1000; $x++) {
				yield new Route('/param/' . $x . '/{id}', new ClosureMiddleware(function ($id) {
					return new Response(200);
				}));
			}
		};

		return $this->__param_routes_collection = new RoutesCollection(...$generator());
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchStaticNanRoutesArrayInsert(): array {
		$routes = [];

		for ($x = 0; $x < 1000; $x++) {
			$path = '/param/' . $x . '/1';
			$routes[$path] = new Route($path, new ClosureMiddleware(function ($id) {
				return new Response(200);
			}));
		}

		return $this->__static_routes_array = $routes;
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchStaticNanRouterInsertManual(): RoutesCollection {
		$generator = function () {
			for ($x = 0; $x < 1000; $x++) {
				yield new Route('/param/' . $x . '/1', new ClosureMiddleware(function ($id) {
					return new Response(200);
				}));
			}
		};

		return $this->__static_routes_collection = new RoutesCollection(...$generator());
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchParamNanRouterLookup(): void {
		$routes = $this->__param_routes_collection;
		$request = new ServerRequestFactory()->createServerRequest('GET', '/param/' . rand(0, 999) . '/1', getallheaders());
		$route = $routes->match($request->getUri()->getPath());
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchParamNanRoutesArrayLookup(): void {
		$routes = $this->__param_routes_array;
		$request = new ServerRequestFactory()->createServerRequest('GET', '/param/' . rand(0, 999) . '/1', getallheaders());

		\array_any($routes, function ($route) use ($request) {
			$path = $request->getUri()->getPath();
			$request_parts = \array_filter(\explode('/', ltrim($path, '/')));
			$route_parts = \array_filter(\explode('/', ltrim($route->path, '/')));

			foreach ($request_parts as $idx => $request_part) {
				$route_part = $route_parts[$idx] ?? null;

				if (\is_null($route_part)) {
					return false;
				}

				if (RoutePattern::checkParameters($route_part)) {
					$request_part = '#';
					$route_part = '#';
				}

				if ($request_part !== $route_part) {
					return false;
				}
			}

			return true;
		});
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchStaticNanRouterLookup(): void {
		$routes = $this->__static_routes_collection;
		$request = new ServerRequestFactory()->createServerRequest('GET', '/param/' . rand(0, 999) . '/1', getallheaders());
		$route = $routes->match($request->getUri()->getPath());
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchStaticNanRoutesArrayLookup(): void {
		$routes = $this->__static_routes_array;
		$request = new ServerRequestFactory()->createServerRequest('GET', '/param/' . rand(0, 999) . '/1', getallheaders());

		\array_any($routes, fn($route) => $route->path === $request->getUri()->getPath());
	}
}
