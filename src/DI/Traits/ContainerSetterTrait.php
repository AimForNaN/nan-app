<?php

namespace NaN\DI\Traits;

trait ContainerSetterTrait {
	public function set(string $id, mixed $value): static {
		$this->_services[$id] = $value;

		return $this;
	}
}
