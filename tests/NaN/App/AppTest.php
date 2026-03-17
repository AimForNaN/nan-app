<?php

use NaN\App;
use NaN\App\Controller\Interfaces\{
	ControllerInterface,
	GetControllerInterface,
};
use NaN\App\Controller\Traits\ControllerTrait;
use NaN\App\Middleware\Router;
use NaN\Http\{
	Request,
	Response,
};
use Psr\Http\Message\{
	ResponseInterface as PsrResponseInterface,
};

describe('App', function () {
	test('Non-existent route', function () {
		$routes = new Router();

		$app = new App();
		$app->use($routes);

		$rsp = $app->handle(new Request('GET', '/bad/route'));
		expect($rsp)->toBeInstanceOf(PsrResponseInterface::class);
		expect($rsp->getStatusCode())->toBe(404);
	});

	test('Route dependency injection (closure)', function () {
		$routes = new Router();
		$routes['/'] = function () {
			return new Response(body: 'good');
		};

		$app = new App();
		$app->use($routes);

		$rsp = $app->handle(new Request('GET', '/'));
		expect($rsp)
			->toBeInstanceOf(PsrResponseInterface::class)
			->and($rsp->getStatusCode())
				->toBe(200)
			->and((string)$rsp->getBody())
				->toBe('good')
		;
	});

	test('Route param injection (closure)', function () {
		$routes = new Router();
		$routes['/{id}'] = function ($id) {
			expect($id)->toBe('1');
			return new Response(body: 'good');
		};

		$app = new App();
		$app->use($routes);

		$rsp = $app->handle(new Request('GET', '/1'));
		expect($rsp)
			->toBeInstanceOf(PsrResponseInterface::class)
			->and($rsp->getStatusCode())
				->toBe(200)
			->and((string)$rsp->getBody())
				->toBe('good')
		;
	});

	test('Route controllers', function () {
		class TestController implements ControllerInterface, GetControllerInterface {
			use ControllerTrait;

			public function get(?int $id = null): PsrResponseInterface {
				expect($id)
					->toBe(1)
					->and($this)
						->toBeInstanceOf(TestController::class)
				;
				return new Response(body: 'good');
			}
		}

		$routes = new Router();
		$routes['/{id}'] = TestController::class;

		$app = new App();
		$app->use($routes);

		$rsp = $app->handle(new Request('GET', '/1'));
		expect($rsp->getStatusCode())
			->toBe(200)
			->and((string)$rsp->getBody())
				->toBe('good')
		;
	});
});
