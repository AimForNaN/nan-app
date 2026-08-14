<?php

use NaN\Application\Controller\{
	Interfaces\ControllerInterface,
	Traits\ControllerTrait,
};
use NaN\Application\Middleware\{
	Router\RouteHandler,
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
	test('Empty services', function () {
		$app = new App();
		$request = new ServerRequestFactory()->createServerRequest('GET', '/');
		$rsp = $app->handle($request);

		expect($rsp->getStatusCode())
			->toBe(404)
		;
	});

	test('Non-existent route', function () {
		$app = new App();
		$request = new ServerRequestFactory()->createServerRequest('GET', '/bad/route');
		$rsp = $app->handle($request);

		expect($rsp->getStatusCode())
				->toBe(404)
		;
	});

	test('Route dependency injection (closure)', function () {
		$app = new App(
			new Container(),
			new RoutesCollection(
				new Route('/', new RouteHandler(function (
					PsrServerRequestInterface $request,
				) {
					expect($request->getAttribute(PsrContainerInterface::class))->toBeNull();

					$rsp = new Response();

					$rsp->getBody()->write('good');

					return $rsp;
				})),
			),
		);
		$request = new ServerRequestFactory()->createServerRequest('GET', '/');
		$rsp = $app->handle($request);

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
				new Route('/{id}', new RouteHandler(function (
					PsrResponseFactoryInterface $factory,
					$id,
				) {
					expect($id)->toBe('1');

					$rsp = $factory->createResponse();

					$rsp->getBody()->write('good');

					return $rsp;
				})),
			),
		);
		$request = new ServerRequestFactory()->createServerRequest('GET', '/1');
		$rsp = $app->handle($request);

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
				expect($id)->toBe(123)
					->and($factory)
						->toBeInstanceOf(PsrResponseFactoryInterface::class)
					->and($request)
						->toBeInstanceOf(PsrServerRequestInterface::class)
					->and($request->getAttribute(PsrContainerInterface::class))
						->toBeNull()
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
				new Route('/{id}', new TestController()),
			),
		);
		$request = new ServerRequestFactory()->createServerRequest('GET', '/123');
		$rsp = $app->handle($request);

		expect($rsp->getStatusCode())->toBe(200)
			->and((string)$rsp->getBody())->toBe('good')
		;

		$rsp = $app->handle($request->withMethod('POST'));

		expect($rsp->getStatusCode())->toBe(405);
	});
});
