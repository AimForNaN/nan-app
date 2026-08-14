<?php

use NaN\Application\Middleware\{
	Router\RoutesCollection,
	Router\Route,
};
use NaN\Collections\Middleware\MiddlewareCollection;

describe('RoutesCollection', function () {
	test('Contains', function () {
		$routes = new RoutesCollection(
			new Route('/nested/deep', new MiddlewareCollection()),
		);

		expect($routes->contains($routes->match('/nested/deep')))->toBeTrue();
	});

	test('Count', function () {
		$routes = new RoutesCollection(
			new Route('/', new MiddlewareCollection()),
			new Route('/nested', new MiddlewareCollection()),
			new Route('/nested/deep', new MiddlewareCollection()),
			new Route('/nested/deep/deeper', new MiddlewareCollection()),
		);

		expect($routes)
			->toHaveCount(4)
			->and($routes->toArray())
				->toHaveCount(4)
		;

		$routes = new RoutesCollection(
			new Route('/', new MiddlewareCollection()),
			new Route('/{id}', new MiddlewareCollection()),
			new Route('/{id}/deep', new MiddlewareCollection()),
			new Route('/{id}/deep/deeper', new MiddlewareCollection()),
			new Route('/nested', new MiddlewareCollection()),
			new Route('/nested/deep', new MiddlewareCollection()),
			new Route('/{name}/deep/deeper', new MiddlewareCollection()),
		);

		expect($routes)
			->toHaveCount(6)
			->and($routes->toArray())
				->toHaveCount(6)
		;
	});

	test('Get named route', function () {
		$route = new Route('/', new MiddlewareCollection(), 'home');
		$routes = new RoutesCollection($route);

		expect($route)->toEqual($routes->matchName('home'));
	});

	test('Static route priority', function () {
		$nested_route = new Route('/nested', new MiddlewareCollection());
		$parameterized_route = new Route('/{id}', new MiddlewareCollection());
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
