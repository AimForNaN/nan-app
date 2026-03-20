<?php

namespace NaN\Http;

class Request extends \GuzzleHttp\Psr7\ServerRequest implements \ArrayAccess {
	public function offsetExists(mixed $offset): bool {
		return (bool)$this->getAttribute($offset);
	}

	public function offsetGet(mixed $offset): mixed {
		return $this->getAttribute($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void {
		throw new \BadMethodCallException('Class is immutable!');
	}

	public function offsetUnset(mixed $offset): void {
		throw new \BadMethodCallException('Class is immutable!');
	}
}
