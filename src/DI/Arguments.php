<?php

namespace NaN\DI;

use NaN\DI\Interfaces\ArgumentInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Arguments implements \Countable, \IteratorAggregate {
	public function __construct(
		protected readonly array $_args = [],
	) {
	}

	public function count(): int {
		return \count($this->_args);
	}

	/**
	 * @throws \ReflectionException
	 */
	static public function fromCallable(callable $callable): self {
		$arguments = static::_analyzeCallable($callable);
		return new self($arguments);
	}

	/**
	 * @throws \ReflectionException
	 */
	static public function fromClassConstructor(string $class): self {
		$arguments = static::_analyzeClassConstructor($class);
		return new self($arguments);
	}

	/**
	 * @throws \ReflectionException
	 */
	static public function fromClassMethod(string $class, string $method): self {
		$arguments = static::_analyzeClassMethod($class, $method);
		return new self($arguments);
	}

	/**
	 * @throws \ReflectionException
	 */
	static public function fromParameter(\ReflectionParameter $param): ArgumentInterface {
		return Argument::fromParameter($param);
	}

	public function getIterator(): \Traversable {
		yield from $this->_args;
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function resolve(array $values, ?PsrContainerInterface $container = null): array {
		$resolved = [];

		/** @var ArgumentInterface $argument */
		foreach ($this as $argument) {
			$name = $argument->getName();

			if (isset($values[$name])) {
				if ($argument->isPrimitive()) {
					$resolved[] = $argument->resolvePrimitive($values[$name]);
				} else {
					$resolved[] = $values[$name];
				}
			} else if ($container) {
				$type = \array_find_key($argument->getTypes(), fn($x) => !$x);

				if ($type) {
					$resolved[] = $container->get($type);
				}
			} else if ($argument->hasDefaultValue()) {
				$resolved[] = $argument->getDefaultValue();
			}
		}

		return $resolved;
	}

	/**
	 * @throws \ReflectionException
	 */
	static protected function _analyzeCallable(callable $callable): array {
		$rf = new \ReflectionFunction($callable);

		return \array_map(static::fromParameter(...), $rf->getParameters());
	}

	/**
	 * @throws \ReflectionException
	 */
	static protected function _analyzeClassConstructor(string $class): array {
		$rf = new \ReflectionClass($class);
		$rf = $rf->getConstructor();

		return \array_map(static::fromParameter(...), $rf->getParameters());
	}

	/**
	 * @throws \ReflectionException
	 */
	static protected function _analyzeClassMethod(string $class, string $method): array {
		$rf = new \ReflectionClass($class);
		$rf = $rf->getMethod($method);

		return \array_map(static::fromParameter(...), $rf->getParameters());
	}
}
