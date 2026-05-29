<?php

namespace NaN\Application\Handlers;

use NaN\Http\{
	ResponseFactory,
	ServerRequest,
};
use Psr\Http\Message\{
	ResponseFactoryInterface as PsrResponseFactoryInterface,
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};
use Psr\Http\Server\RequestHandlerInterface as PsrRequestHandlerInterface;

class NotFoundHandler implements PsrRequestHandlerInterface {
	public function handle(PsrServerRequestInterface $request): PsrResponseInterface {
		$response_factory = ServerRequest::getServiceFromRequest(
			PsrResponseFactoryInterface::class,
			$request,
		);
		$response_factory ??= new ResponseFactory();

		return $response_factory->createResponse(404);
	}
}
