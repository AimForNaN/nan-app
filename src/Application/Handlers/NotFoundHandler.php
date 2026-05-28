<?php

namespace NaN\Application\Handlers;

use NaN\Http\ResponseFactory;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\{
	ResponseFactoryInterface as PsrResponseFactoryInterface,
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};
use Psr\Http\Server\RequestHandlerInterface as PsrRequestHandlerInterface;

class NotFoundHandler implements PsrRequestHandlerInterface {
	public function handle(PsrServerRequestInterface $request): PsrResponseInterface {
		$services = $request->getAttribute(PsrContainerInterface::class);

		if (
			$services instanceof PsrContainerInterface &&
			$services->has(PsrResponseFactoryInterface::class)
		) {
			$response_factory = $services->get(PsrResponseFactoryInterface::class);
		}

		$response_factory ??= new ResponseFactory();

		return $response_factory->createResponse(404);
	}
}
