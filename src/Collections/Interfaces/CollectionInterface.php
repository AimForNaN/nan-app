<?php

namespace NaN\Collections\Interfaces;

interface CollectionInterface extends \Countable, \IteratorAggregate {
	public function any(callable $fn): bool;

	public function every(callable $fn): bool;

	public function filter(callable $filter): \Traversable;

	public function find(callable $fn): mixed;

	public function implode(string $delimiter): string;

	public function map(callable $fn): \Traversable;

	public function reduce(callable $fn, mixed $initial_value = null): mixed;

	public function toArray(): array;
}
