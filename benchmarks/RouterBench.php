<?php

use NaN\App\Middleware\Router\{Route, RoutesCollection};
use NaN\Http\{
	Request,
	Response,
};

class RouterBench {
	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchParamNanRoutesArrayInsert(): array {
		$routes = [];

		for ($x = 0; $x < 1000; $x++) {
			$routes[] = new Route('/param/' . $x . '/{id}', function ($id) {
				return new Response(200);
			});
		}

		return $routes;
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchParamNanRouterInsertManual(): RoutesCollection {
		$generator = function () {
			for ($x = 0; $x < 1000; $x++) {
				yield new Route('/param/' . $x . '/{id}', function ($id) {
					return new Response(200);
				});
			}
		};

		return new RoutesCollection(...$generator());
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
			$routes[$path] = new Route($path, function ($id) {
				return new Response(200);
			});
		}

		return $routes;
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchStaticNanRouterInsertManual(): RoutesCollection {
		$generator = function () {
			for ($x = 0; $x < 1000; $x++) {
				yield new Route('/param/' . $x . '/1', function ($id) {
					return new Response(200);
				});
			}
		};

		return new RoutesCollection(...$generator());
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchParamNanRouterLookup(): void {
		$routes = $this->benchParamNanRouterInsertManual();
		$request = new Request('GET', '/param/' . rand(0, 999) . '/1', getallheaders());
		$route = $routes->match($request->getUri()->getPath());
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchParamNanRoutesArrayLookup(): void {
		$routes = $this->benchParamNanRoutesArrayInsert();
		$request = new Request('GET', '/param/' . rand(0, 999) . '/1', getallheaders());

		foreach ($routes as $route) {
			if ($route->matchesRequest($request)) {
				return;
			}
		}
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchStaticNanRouterLookup(): void {
		$routes = $this->benchStaticNanRouterInsertManual();
		$request = new Request('GET', '/param/' . rand(0, 999) . '/1', getallheaders());
		$route = $routes->match($request->getUri()->getPath());
	}

	/**
	 * @Iterations(20)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchStaticNanRoutesArrayLookup(): void {
		$routes = $this->benchStaticNanRoutesArrayInsert();
		$request = new Request('GET', '/param/' . rand(0, 999) . '/1', getallheaders());

		foreach ($routes as $route) {
			if ($route->matchesRequest($request)) {
				return;
			}
		}
	}
}
