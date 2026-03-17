<?php

use NaN\App\Middleware\Router;
use NaN\App\Middleware\Router\Route;

describe('Router', function () {
	test('Adding routes (index)', function () {
		$routes = new Router();
		$routes['/nested/route'] = function () {};
		$route = $routes['/nested/route'];

		expect($route)->toBeInstanceOf(Route::class);
	});

	test('Adding routes (manually)', function () {
		$root = new Route('/', null, [
			'nested' => new Route('/nested', null, [
				'route' => new Route('/nested/route', function () {}),
			]),
		]);

		$routes = new Router($root);
		$route = $routes['/nested/route'];

		expect($route)->toBeInstanceOf(Route::class);
	});

	test('Contains', function () {
		$routes = new Router();
		$route = new Route('/nested/deep');

		$routes->insertRoute($route);

		expect($routes->contains($routes->root))
			->toBeTrue()
			->and($routes->contains($route))
				->toBeTrue()
		;
	});

	test('Count', function () {
		$routes = new Router();
		$routes['/nested'] = function () {};
		$routes['/nested/deep'] = function () {};
		$routes['/nested/deep/deeper'] = function () {};

		expect(\iterator_count($routes))->toBe(4);
	});

	test('Get named route', function () {
		$routes = new Router();

		$routes->setName('home', $routes->root);

		$get = $routes->matchName('home');

		expect($get)->toEqual($routes->root);
	});

	test('Get non-existent route', function () {
		$routes = new Router();
		$route = $routes['/bad/route'];

		expect($route)->toBeNull();
	});
});
