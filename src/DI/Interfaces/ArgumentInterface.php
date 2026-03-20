<?php

namespace NaN\DI\Interfaces;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ArgumentInterface {
	public function getDefaultValue(): mixed;

	public function getName(): string;

	public function getTypes(): array;

	public function hasDefaultValue(): bool;

	public function hasType(?string $type = null): bool;

	public function isNullable(): bool;

	public function isOptional(): bool;

	public function isPrimitive(): bool;

	public function isVariadic(): bool;

	public function resolvePrimitive(mixed $value): mixed;
}
