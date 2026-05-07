<?php

use NaN\App\Middleware\{
	Router\Route,
	Router\RoutesCollection,
};

describe('RoutesCollection', function () {
	test('Contains', function () {
		$routes = new RoutesCollection(
			new Route('/nested/deep'),
		);

		expect($routes->contains($routes->match('/nested/deep')))->toBeTrue();
	});

	test('Count', function () {
		$routes = new RoutesCollection(
			new Route('/'),
			new Route('/nested'),
			new Route('/nested/deep'),
			new Route('/nested/deep/deeper'),
		);

		expect($routes)
			->toHaveCount(4)
			->and($routes->toArray())
				->toHaveCount(4)
		;

		$routes = new RoutesCollection(
			new Route('/'),
			new Route('/{id}'),
			new Route('/{id}/deep'),
			new Route('/{id}/deep/deeper'),
			new Route('/nested'),
			new Route('/nested/deep'),
			new Route('/{name}/deep/deeper'),
		);

		expect($routes)
			->toHaveCount(6)
			->and($routes->toArray())
				->toHaveCount(6)
		;
	});

	test('Get named route', function () {
		$route = new Route('/', null, 'home');
		$routes = new RoutesCollection($route);

		expect($route)->toEqual($routes->matchName('home'));
	});

	test('Static route priority', function () {
		$nested_route = new Route('/nested');
		$parameterized_route = new Route('/{id}');
		$routes = new RoutesCollection(
			$parameterized_route,
			$nested_route,
		);

		expect($routes->match('/nested'))
			->toBe($nested_route)
			->and($routes->match('/parameterized'))
				->toBe($parameterized_route)
		;
	});
});
