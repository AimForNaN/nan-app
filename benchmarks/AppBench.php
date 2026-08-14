<?php

use NaN\Application\Middleware\Router\{
	Route,
	RouteHandler,
	RoutesCollection,
};
use NaN\Application\NativeApplication as App;
use NaN\Http\Response;

class AppBench {
	/**
	 * @Iterations(10)
	 * @Revs(10)
	 */
	public function benchNanAppStartup(): void {
		$app = new App();
	}

	/**
	 * @Iterations(10)
	 * @Revs(10)
	 * @Warmup(1)
	 */
	public function benchNanAppRun(): void {
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/';

		$router = new RoutesCollection(
			new Route('/', new RouteHandler(function () {
				return new Response();
			})),
		);

		$app = new App()->withMiddleware($router);
		$app->run();
	}
}
