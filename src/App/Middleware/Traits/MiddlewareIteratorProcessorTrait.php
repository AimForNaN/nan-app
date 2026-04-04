<?php

namespace NaN\App\Middleware\Traits;

use Psr\Http\Message\{
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};
use Psr\Http\Server\{
	MiddlewareInterface as PsrMiddlewareInterface,
	RequestHandlerInterface as PsrRequestHandlerInterface,
};

/**
 * @implements \Iterator
 * @implements PsrMiddlewareInterface
 */
trait MiddlewareIteratorProcessorTrait {
	use MiddlewareIteratorTrait;

	public function process(
		PsrServerRequestInterface $request,
		PsrRequestHandlerInterface $handler,
	): PsrResponseInterface {
		if (!$this->valid()) {
			return $handler->handle($request);
		}

		$current = $this->current();
		$this->next();

		return $current->process($request, $this);
	}
}
