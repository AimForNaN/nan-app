<?php

namespace NaN\Application\Interfaces;

use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;

interface MiddlewareApplicationInterface extends ApplicationInterface {
	public PsrMiddlewareInterface $middleware {
		get;
	}

	public function withMiddleware(PsrMiddlewareInterface $middleware): MiddlewareApplicationInterface;
}
