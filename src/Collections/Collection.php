<?php

namespace NaN\Collections;

class Collection implements Interfaces\CollectionInterface {
	protected array $_data = [];

	/**
	 * @param mixed ...$data
	 */
	public function __construct(
		mixed ...$data,
	) {
		$this->_data = $data;
	}

	public function any(callable $fn): bool {
		return \iter\any($fn, $this->getIterator());
	}

	public function count(): int {
		return \iterator_count($this->getIterator());
	}

	public function every(callable $fn): bool {
		return \iter\all($fn, $this->getIterator());
	}

	public function filter(callable $fn): \Traversable {
		return \iter\filter($fn, $this->getIterator());
	}

	public function find(callable $fn): mixed {
		return \iter\search($fn, $this->getIterator());
	}

	public function getIterator(): \Traversable {
		yield from $this->_data;
	}

	public function implode(string $delimiter): string {
		return \implode($delimiter, $this->_data);
	}

	public function map(callable $fn): \Traversable {
		return \iter\map($fn, $this->getIterator());
	}

	public function reduce(callable $fn, mixed $initial_value = null): mixed {
		return \iter\reduce($fn, $this->getIterator(), $initial_value);
	}

	public function toArray(): array {
		return \iterator_to_array($this->getIterator());
	}
}
