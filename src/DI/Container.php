<?php

namespace NaN\DI;

use Psr\Container\ContainerInterface as PsrContainerInterface;

class Container implements \Countable, \IteratorAggregate, PsrContainerInterface {
	use Traits\ContainerDelegatesTrait;

	public function __construct(
		array $services = [],
		iterable $delegates = [],
	) {
		$this->_services = $services;
		$this->addDelegates(...$delegates);
	}

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
