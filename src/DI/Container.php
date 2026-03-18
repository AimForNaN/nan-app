<?php

namespace NaN\DI;

use Psr\Container\{
	ContainerExceptionInterface,
	ContainerInterface as PsrContainerInterface,
	NotFoundExceptionInterface,
};

class Container implements \Countable, Interfaces\ContainerInterface, \IteratorAggregate {
	protected array $_delegates = [];

	public function __construct(
		protected array $_data = [],
	) {
	}

	public function addDelegates(PsrContainerInterface ...$delegates): static {
		foreach ($delegates as $delegate) {
			$this->_delegates[] = $delegate;
		}

		return $this;
	}

	public function count(): int {
		$count = \count($this->_data);
		$count += \iterator_count($this);

		return $count;
	}

	public function get(string $id): mixed {
		$entry = $this->_data[$id] ?? null;

		if ($entry) {
			return $this->_resolve($entry);
		}

		$delegate = \iter\search(fn($delegate) => $delegate->has($id), $this);

		if ($delegate) {
			return $delegate->get($id);
		}

		throw new Exceptions\NotFoundException("Entity {$id} could not be found!");
	}

	/**
	 * Yields delegates.
	 *
	 * @return \Traversable
	 */
	public function getIterator(): \Traversable {
		yield from $this->_delegates;
	}

	public function has(string $id): bool {
		if (isset($this->_data[$id])) {
			return true;
		}

		return \iter\any(fn($container) => $container->has($id), $this);
	}

	public function offsetExists(mixed $offset): bool {
		return $this->has($offset);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function offsetGet(mixed $offset): mixed {
		return $this->get($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void {
		if (\is_null($offset) && \is_string($value)) {
			$offset = $value;
		}

		$this->set($offset, $value);
	}

	public function set(string $id, mixed $value): static {
		$this->_data[$id] = $value;

		return $this;
	}

	protected function _resolve(mixed $value): mixed {
		if ($value instanceof \Closure) {
			$value = \Closure::bind($value, $this);
			return $value();
		}

		if (\is_string($value)) {
			return new $value();
		}

		return $value;
	}
}
