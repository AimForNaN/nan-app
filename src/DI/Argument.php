<?php

namespace NaN\DI;

use AllowDynamicProperties;
use NaN\DI\Interfaces\ArgumentInterface;
use Psr\Container\{
	ContainerExceptionInterface as PsrContainerExceptionInterface,
	ContainerInterface as PsrContainerInterface,
	NotFoundExceptionInterface,
};

class Argument implements ArgumentInterface {
	protected mixed $_default_value;
	protected bool $_has_default_value = false;
	protected string $_name;
	protected bool $_nullable = false;
	protected bool $_optional  = false;
	protected array $_types = [];
	protected bool $_variadic = false;

	static public function fromParameter(\ReflectionParameter $param): ArgumentInterface {
		$arg = new Argument();
		$arg->_name = $param->getName();
		$arg->_has_default_value = $param->isDefaultValueAvailable();
		$arg->_nullable = $param->allowsNull();
		$arg->_optional = $param->isOptional();
		$arg->_variadic = $param->isVariadic();

		if ($param->hasType()) {
			$arg->_types = self::parseType($param->getType());
		}

		if ($arg->_optional) {
			$arg->_default_value = $param->getDefaultValue();
		}

		return $arg;
	}

	/**
	 * @throws \ReflectionException
	 */
	public function getDefaultValue(): mixed {
		return $this->_default_value;
	}

	public function getName(): string {
		return $this->_name;
	}

	public function getTypes(): array {
		return $this->_types;
	}

	public function hasDefaultValue(): bool {
		return $this->_has_default_value;
	}

	public function hasType(?string $type = null): bool {
		if (empty($type)) {
			return !empty($this->_types);
		}

		return \array_any($this->_types, fn($value, $key) => $key === $type);
	}

	public function isNullable(): bool {
		return $this->_nullable;
	}

	public function isOptional(): bool {
		return $this->_optional or $this->_nullable or $this->_has_default_value;
	}

	public function isPrimitive(): bool {
		return \array_all($this->_types, fn($x) => $x);
	}

	public function isVariadic(): bool {
		return $this->_variadic;
	}

	static public function parseType(\ReflectionType $type): array {
		$types = [];

		if (
			$type instanceof \ReflectionUnionType or
			$type instanceof \ReflectionIntersectionType
		) {
			foreach ($type->getTypes() as $type) {
				$types[$type->getName()] = $type->isBuiltin();
			}
		} else if ($type instanceof \ReflectionNamedType) {
			$types[$type->getName()] = $type->isBuiltin();
		}

		return $types;
	}

	/**
	 * @throws PsrContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function resolvePrimitive(mixed $value): mixed {
		if (!$this->hasType()) {
			return $value;
		}

		$type = \array_find($this->_types, fn($x) => $x);

		return match ($type) {
			'bool', 'boolean' => self::resolveToBoolean($value),
			'double', 'float' => self::resolveToFloat($value),
			'int', 'integer' => self::resolveToInteger($value),
			'string' => self::resolveToString($value),
			default => $value,
		};
	}

	static public function resolveToBoolean(mixed $value): bool {
		return \filter_var($value, \FILTER_VALIDATE_BOOL);
	}

	static public function resolveToFloat(mixed $value): float {
		return (float)$value;
	}

	static public function resolveToInteger(mixed $value): int {
		return (int)$value;
	}

	static public function resolveToString(mixed $value): string {
		return (string)$value;
	}
}
