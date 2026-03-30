<?php

use NaN\App;
use NaN\App\Middleware\{Router, Router\Route, Router\RoutesCollection};
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

		$router = new Router(new RoutesCollection(
			new Route('/', function () {
				return new Response();
			}),
		));

		$app = new App()->withMiddleware($router);
		$app->run();
	}
}
