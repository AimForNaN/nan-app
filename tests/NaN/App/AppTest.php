<?php

use NaN\App;
use NaN\App\Controller\{
	Interfaces\ControllerInterface,
	Traits\ControllerTrait,
};
use NaN\App\Middleware\Router\{Route, RoutesCollection};
use NaN\DI\Container;
use NaN\Http\{
	Response,
	ResponseFactory,
	ServerRequestFactory,
};
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\{
	ResponseFactoryInterface as PsrResponseFactoryInterface,
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};

describe('App', function () {
	test('Non-existent route', function () {
		$app = new App(
			new Container([
				PsrResponseFactoryInterface::class => new ResponseFactory(),
			]),
			new RoutesCollection(),
		);
		$request = new ServerRequestFactory()->createServerRequest('GET', '/bad/route');
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

				$rsp = new Response();

				$rsp->getBody()->write('good');

				return $rsp;
			}),
		);
		$app = new App(
			new Container([
				PsrServerRequestInterface::class => new ServerRequestFactory()->createServerRequest(
					'GET',
					'/',
				),
			]),
			$routes,
		);
		$rsp = $app->run();

		expect($app->services->get(PsrServerRequestInterface::class))->toBeInstanceOf(PsrServerRequestInterface::class)
			->and($rsp)->toBeInstanceOf(PsrResponseInterface::class)
			->and($rsp->getStatusCode())->toBe(200)
			->and((string)$rsp->getBody())->toBe('good')
		;
	});

	test('Route param injection (closure)', function () {
		$routes = new RoutesCollection(
			new Route('/{id}', function ($id) {
				expect($id)->toBe('1');

				$rsp = new Response();

				$rsp->getBody()->write('good');

				return $rsp;
			}),
		);
		$app = new App(
			new Container([
				PsrServerRequestInterface::class => new ServerRequestFactory()->createServerRequest(
					'GET',
					'/1',
				),
			]),
			$routes,
		);
		$rsp = $app->run();
		/** @var PsrServerRequestInterface $server_request */
		$server_request = $app->services->get(PsrServerRequestInterface::class);

		expect($server_request)->toBeInstanceOf(PsrServerRequestInterface::class)
			->and($server_request->getUri()->getPath())->toBe('/1')
			->and($rsp)->toBeInstanceOf(PsrResponseInterface::class)
			->and($rsp->getStatusCode())->toBe(200)
			->and((string)$rsp->getBody())->toBe('good')
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

				$rsp = new Response();

				$rsp->getBody()->write('good');

				return $rsp;
			}
		}

		$routes = new RoutesCollection(
			new Route('/{id}', TestController::class),
		);
		$app = new App(
			new Container([
				PsrServerRequestInterface::class => new ServerRequestFactory()->createServerRequest(
					'GET',
					'/123',
				),
			]),
			$routes,
		);

		$rsp = $app->run();
		/** @var PsrServerRequestInterface $server_request */
		$server_request = $app->services->get(PsrServerRequestInterface::class);

		expect($server_request)->toBeInstanceOf(PsrServerRequestInterface::class)
			->and($server_request->getUri()->getPath())->toBe('/123')
			->and($rsp)->toBeInstanceOf(PsrResponseInterface::class)
			->and($rsp->getStatusCode())->toBe(200)
			->and((string)$rsp->getBody())->toBe('good')
		;
	});
});
