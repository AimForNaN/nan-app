<?php

namespace NaN\App\Middleware;

use NaN\App\Middleware\Traits\MiddlewareIteratorProcessorTrait;
use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;

class MiddlewareCollection implements \Iterator, PsrMiddlewareInterface {
	use MiddlewareIteratorProcessorTrait;

	public function __construct(PsrMiddlewareInterface ...$middleware) {
		$this->_middleware = $middleware;
	}
}
