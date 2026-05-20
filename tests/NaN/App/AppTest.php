<?php

use NaN\Application\Controller\{
	Interfaces\ControllerInterface,
	Traits\ControllerTrait,
};
use NaN\Application\Middleware\{
	Router\RoutesCollection,
	Router\Route,
};
use NaN\Application\NativeApplication as App;
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
		$rsp = $app->handle($request->withAttribute(PsrContainerInterface::class, $app->services));

		expect($rsp)
			->toBeInstanceOf(PsrResponseInterface::class)
			->and($rsp->getStatusCode())
				->toBe(404)
		;
	});

	test('Route dependency injection (closure)', function () {
		$app = new App(
			new Container([
				PsrResponseFactoryInterface::class => new ResponseFactory(),
			]),
			new RoutesCollection(
				new Route('/', function (PsrServerRequestInterface $request) {
					expect($request->getAttribute(PsrContainerInterface::class))
							->toBeInstanceOf(PsrContainerInterface::class)
					;

					$rsp = new Response();

					$rsp->getBody()->write('good');

					return $rsp;
				}),
			),
		);
		$request = new ServerRequestFactory()->createServerRequest('GET', '/');
		$rsp = $app->handle($request->withAttribute(PsrContainerInterface::class, $app->services));

		expect($rsp->getStatusCode())->toBe(200)
			->and((string)$rsp->getBody())->toBe('good')
		;
	});

	test('Route param injection (closure)', function () {
		$app = new App(
			new Container([
				PsrResponseFactoryInterface::class => new ResponseFactory(),
			]),
			new RoutesCollection(
				new Route('/{id}', function (PsrResponseFactoryInterface $factory, $id) {
					expect($id)->toBe('1');

					$rsp = $factory->createResponse();

					$rsp->getBody()->write('good');

					return $rsp;
				}),
			),
		);
		$request = new ServerRequestFactory()->createServerRequest('GET', '/1');
		$rsp = $app->handle($request->withAttribute(PsrContainerInterface::class, $app->services));

		expect($rsp->getStatusCode())->toBe(200)
			->and((string)$rsp->getBody())->toBe('good')
		;
	});

	test('Route controllers', function () {
		class TestController implements ControllerInterface {
			use ControllerTrait;

			public function get(
				?PsrResponseFactoryInterface $factory = null,
				?PsrServerRequestInterface $request = null,
				?int $id = null,
			): PsrResponseInterface {
				expect($id)
					->toBe(123)
					->and($request->getAttribute(PsrContainerInterface::class))
						->toBeInstanceOf(PsrContainerInterface::class)
					->and($this)
						->toBeInstanceOf(TestController::class)
				;

				$rsp = $factory->createResponse();

				$rsp->getBody()->write('good');

				return $rsp;
			}
		}

		$app = new App(
			new Container([
				PsrResponseFactoryInterface::class => new ResponseFactory(),
			]),
			new RoutesCollection(
				new Route('/{id}', TestController::class),
			),
		);
		$request = new ServerRequestFactory()->createServerRequest('GET', '/123');
		$request = $request->withAttribute(PsrContainerInterface::class, $app->services);
		$rsp = $app->handle($request);

		expect($rsp->getStatusCode())->toBe(200)
			->and((string)$rsp->getBody())->toBe('good')
		;

		$rsp = $app->handle($request->withMethod('POST'));

		expect($rsp->getStatusCode())->toBe(405);
	});
});
