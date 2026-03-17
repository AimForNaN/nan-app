<?php

namespace NaN\DI;

use NaN\DI\Interfaces\ContainerInterface;
use Psr\Container\{
	ContainerInterface as PsrContainerInterface,
};

class Container extends \NaN\Collections\Collection implements ContainerInterface {
	protected array $delegates = [];

	public function addDelegate(PsrContainerInterface $container) {
		$this->delegates[] = $container;
	}

	public function get(string $id): mixed {
		$entry = $this->data[$id] ?? null;

		if (!$entry) {
			foreach ($this->data as $container_entry) {
				if (\is_a($container_entry, $id)) {
					$entry = $container_entry;
					break;
				}
			}
		}

		if ($entry) {
			return $this->resolve($entry);
		}

		foreach ($this->delegates as $delegate) {
			if ($delegate->has($id)) {
				return $delegate->get($id);
			}
		}

		throw new Exceptions\NotFoundException("Entity {$id} could not be found!");
	}

	public function getIterator(): \Traversable {
		$iter = new \AppendIterator();

		$iter->append(new \ArrayIterator($this->data));

		foreach ($this->delegates as $delegate) {
			$iter->append(new \ArrayIterator($delegate->data));
		}

		return $iter;
	}

	public function has(string $id): bool {
		if (isset($this->data[$id])) {
			return true;
		}

		foreach ($this->data as $entry) {
			if (\is_a($entry, $id)) {
				return true;
			}
		}

		return \array_any($this->delegates, fn($delegate) => $delegate->has($id));
	}

	public function offsetExists(mixed $offset): bool {
		return $this->has($offset);
	}

	public function offsetGet(mixed $offset): mixed {
		return $this->get($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void {
		if (\is_null($offset) && \is_string($value)) {
			$offset = $value;
		}

		parent::offsetSet($offset, $value);
	}

	protected function resolve(mixed $value): mixed {
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
