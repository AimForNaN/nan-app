<?php

namespace NaN\Application\Controller\Traits;

use NaN\Application\Controller\Interfaces\{
	ConnectControllerInterface,
	DeleteControllerInterface,
	OptionsControllerInterface,
	PatchControllerInterface,
	PostControllerInterface,
	PutControllerInterface,
	TraceControllerInterface,
};
use NaN\DI\{
	Arguments,
	DelegatesContainer,
};
use NaN\Http\{
	Response,
	ResponseFactory,
	ServerRequest,
	Streams\TempStream,
};
use Psr\Container\{
	ContainerExceptionInterface as PsrContainerExceptionInterface,
	ContainerInterface as PsrContainerInterface,
	NotFoundExceptionInterface as PsrNotFoundExceptionInterface,
};
use Psr\Http\{
	Message\ResponseFactoryInterface as PsrResponseFactoryInterface,
	Message\ResponseInterface as PsrResponseInterface,
	Message\ServerRequestInterface as PsrServerRequestInterface,
	Server\MiddlewareInterface as PsrMiddlewareInterface,
	Server\RequestHandlerInterface as PsrRequestHandlerInterface,
};

/**
 * @implements PsrMiddlewareInterface
 */
trait ControllerTrait {
	public function get(): PsrResponseInterface {
		return new Response(501);
	}

	public function getAllowedMethods(): array {
		$allowed_methods = [
			'GET' => 'GET',
			'HEAD' => 'HEAD',
		];

		if ($this instanceof ConnectControllerInterface) {
			$allowed_methods['CONNECT'] = 'CONNECT';
		}

		if ($this instanceof DeleteControllerInterface) {
			$allowed_methods['DELETE'] = 'DELETE';
		}

		if ($this instanceof OptionsControllerInterface) {
			$allowed_methods['OPTIONS'] = 'OPTIONS';
		}

		if ($this instanceof PatchControllerInterface) {
			$allowed_methods['PATCH'] = 'PATCH';
		}

		if ($this instanceof PostControllerInterface) {
			$allowed_methods['POST'] = 'POST';
		}

		if ($this instanceof PutControllerInterface) {
			$allowed_methods['PUT'] = 'PUT';
		}

		if ($this instanceof TraceControllerInterface) {
			$allowed_methods['TRACE'] = 'TRACE';
		}

		return $allowed_methods;
	}

	public function head(...$args): PsrResponseInterface {
		return $this->get(...$args)->withBody(new TempStream());
	}

	/**
	 * @throws PsrContainerExceptionInterface
	 * @throws \ReflectionException
	 * @throws PsrNotFoundExceptionInterface
	 */
	public function process(
		PsrServerRequestInterface $request,
		PsrRequestHandlerInterface $handler,
	): PsrResponseInterface {
		$allowed_methods = $this->getAllowedMethods();
		$method = $request->getMethod();

		if (isset($allowed_methods[$method])) {
			$delegates = [];
			$method = \strtolower($method);

			if ($services = $request->getAttribute(PsrContainerInterface::class)) {
				$delegates[] = $services;
			}

			$container = new DelegatesContainer([
				PsrRequestHandlerInterface::class => $handler,
				PsrServerRequestInterface::class => $request->withoutAttribute(PsrContainerInterface::class),
			])->withDelegates(...$delegates);
			$args = Arguments::fromClassMethod($this, $method);
			$resolved = $args->resolve($request->getQueryParams(), $container);

			return $this->$method(...$resolved);
		}

		/** @var PsrResponseFactoryInterface $response_factory */
		$response_factory = ServerRequest::getServiceFromRequest(
			PsrResponseFactoryInterface::class,
			$request,
			ResponseFactory::class,
		);

		return $response_factory->createResponse(405)
			->withHeader('Allow', implode(', ', $allowed_methods))
		;
	}
}
