<?php

namespace NaN\DI;

use NaN\DI\Traits\ContainerTrait;
use Psr\Container\ContainerInterface as PsrContainerInterface;

class Container implements \Countable, \IteratorAggregate, PsrContainerInterface {
	use ContainerTrait;

	public function count(): int {
		$count = \count($this->_services);
		$count += \iterator_count($this->getIterator());

		return $count;
	}

	public function set(string $id, mixed $value): static {
		$this->_services[$id] = $value;

		return $this;
	}
}
