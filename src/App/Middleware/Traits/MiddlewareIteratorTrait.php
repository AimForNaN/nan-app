<?php

namespace NaN\App\Middleware\Traits;

use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;

trait MiddlewareIteratorTrait {
	protected iterable $_middleware = [];

	public function current(): mixed {
		return \current($this->_middleware);
	}

	public function key(): mixed {
		return \key($this->_middleware);
	}

	public function next(): void {
		\next($this->_middleware);
	}

	public function rewind(): void {
		\reset($this->_middleware);
	}

	public function valid(): bool {
		return \is_subclass_of(\current($this->_middleware), PsrMiddlewareInterface::class);
	}
}
