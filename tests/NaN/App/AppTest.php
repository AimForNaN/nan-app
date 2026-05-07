<?php

use NaN\App;
use NaN\App\Controller\Interfaces\ControllerInterface;
use NaN\App\Controller\Traits\ControllerTrait;
use NaN\App\Middleware\Router\{Route, RoutesCollection};
use NaN\Http\{Request, Response};
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\{
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};

describe('App', function () {
	test('Non-existent route', function () {
		$app = new App()->withMiddleware(new RoutesCollection());
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
		$routes = new RoutesCollection(
			new Route('/', function (PsrServerRequestInterface $request) {
				expect($request)
					->toBeInstanceOf(PsrServerRequestInterface::class)
					->and($request->getAttribute(PsrContainerInterface::class))
					->toBeInstanceOf(PsrContainerInterface::class)
				;

				return new Response(body: 'good');
			}),
		);

		$app = new App()->withMiddleware($routes);
		$request = new Request('GET', '/')
			->withAttribute(PsrContainerInterface::class, $app->services)
		;

		$rsp = $app->run($request);
		expect($rsp)
			->toBeInstanceOf(PsrResponseInterface::class)
			->and($rsp->getStatusCode())
				->toBe(200)
			->and((string)$rsp->getBody())
				->toBe('good')
		;
	});

	test('Route param injection (closure)', function () {
		$routes = new RoutesCollection(
			new Route('/{id}', function ($id) {
				expect($id)->toBe('1');
				return new Response(body: 'good');
			}),
		);

		$app = new App()->withMiddleware($routes);
		$request = new Request('GET', '/1')
			->withAttribute(PsrContainerInterface::class, $app->services)
		;
		$rsp = $app->run($request);

		expect($rsp)
			->toBeInstanceOf(PsrResponseInterface::class)
			->and($rsp->getStatusCode())
				->toBe(200)
			->and((string)$rsp->getBody())
				->toBe('good')
		;
	});

	test('Route controllers', function () {
		class TestController implements ControllerInterface {
			use ControllerTrait;

			public function get(?PsrServerRequestInterface $request = null, ?int $id = null): PsrResponseInterface {
				expect($id)
					->toBe(123)
					->and($request)
						->toBeInstanceOf(PsrServerRequestInterface::class)
					->and($request->getAttribute(PsrContainerInterface::class))
						->toBeInstanceOf(PsrContainerInterface::class)
					->and($this)
						->toBeInstanceOf(TestController::class)
				;
				return new Response(body: 'good');
			}
		}

		$routes = new RoutesCollection(
			new Route('/{id}', TestController::class),
		);

		$app = new App()->withMiddleware($routes);
		$request = new Request('GET', '/123')
			->withAttribute(PsrContainerInterface::class, $app->services)
		;

		$rsp = $app->run($request);
		expect($rsp->getStatusCode())
			->toBe(200)
			->and((string)$rsp->getBody())
				->toBe('good')
		;
	});
});
