<?php

namespace NaN\DI\Traits;

use NaN\DI\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface as PsrContainerInterface;

trait ContainerDelegatesTrait {
	use ContainerTrait;

	protected array $_delegates = [];

	public function addDelegates(PsrContainerInterface ...$delegates): static {
		foreach ($delegates as $delegate) {
			$this->_delegates[] = $delegate;
		}

		return $this;
	}

	public function get(string $id): mixed {
		$entry = $this->_services[$id] ?? null;

		if ($entry) {
			return $this->_resolve($entry);
		}

		$delegate = \iter\search(fn($delegate) => $delegate->has($id), $this->getIterator());

		if ($delegate) {
			return $delegate->get($id);
		}

		throw new NotFoundException("Entity {$id} could not be found!");
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
		if (isset($this->_services[$id])) {
			return true;
		}

		return \iter\any(fn($container) => $container->has($id), $this->getIterator());
	}
}
