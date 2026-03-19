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
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\{
	ResponseInterface as PsrResponseInterface,
};

describe('App', function () {
	test('Non-existent route', function () {
		$routes = new Router();

		$app = new App()->withMiddleware($routes);
		$request = new Request('GET', '/bad/route')
			->withAttribute(PsrContainerInterface::class, $app->services)
		;

		$rsp = $app->handle($request);
		expect($rsp)
			->toBeInstanceOf(PsrResponseInterface::class)
			->and($rsp->getStatusCode())
				->toBe(404)
		;
	});

	test('Route dependency injection (closure)', function () {
		$routes = new Router();
		$routes['/'] = function () {
			return new Response(body: 'good');
		};

		$app = new App()->withMiddleware($routes);
		$request = new Request('GET', '/')
			->withAttribute(PsrContainerInterface::class, $app->services)
		;

		$rsp = $app->handle($request);
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

		$app = new App()->withMiddleware($routes);
		$request = new Request('GET', '/1')
			->withAttribute(PsrContainerInterface::class, $app->services)
		;

		$rsp = $app->handle($request);
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

		$app = new App()->withMiddleware($routes);
		$request = new Request('GET', '/1')
			->withAttribute(PsrContainerInterface::class, $app->services)
		;

		$rsp = $app->handle($request);
		expect($rsp->getStatusCode())
			->toBe(200)
			->and((string)$rsp->getBody())
				->toBe('good')
		;
	});
});
