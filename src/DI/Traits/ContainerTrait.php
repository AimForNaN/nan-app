<?php

namespace NaN\DI\Traits;

use NaN\DI\Exceptions\NotFoundException;

trait ContainerTrait {
	protected array $_services = [];

	public function count(): int {
		return \count($this->_services);
	}

	public function get(string $id): mixed {
		$entry = $this->_services[$id] ?? null;

		if ($entry) {
			return $this->_resolve($entry);
		}

		throw new NotFoundException("Entity {$id} could not be found!");
	}

	public function has(string $id): bool {
		return isset($this->_services[$id]);
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
