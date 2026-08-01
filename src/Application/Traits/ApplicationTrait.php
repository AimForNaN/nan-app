<?php

namespace NaN\Application\Traits;

use NaN\Http\RequestHandlers\NotFoundRequestHandler;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\{
	Message\ResponseInterface as PsrResponseInterface,
	Message\ServerRequestInterface as PsrServerRequestInterface,
	Server\MiddlewareInterface as PsrMiddlewareInterface,
};

/**
 * @property PsrMiddlewareInterface $middleware
 * @property PsrContainerInterface $services
 */
trait ApplicationTrait {
	public function handle(PsrServerRequestInterface $request): PsrResponseInterface {
		$request = $request->withAttribute(
			PsrContainerInterface::class,
			$this->services,
		);

		return $this->middleware->process(
			$request,
			new NotFoundRequestHandler(),
		);
	}
}
