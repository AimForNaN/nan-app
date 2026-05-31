<?php

namespace NaN;

/**
 * Manange environment variables.
 */
class Env {
	static protected array $aliases = [];
	protected array $_env = [];

	public function __construct(?string $dir = null) {
		$repo = \Dotenv\Repository\RepositoryBuilder::createWithNoAdapters()->immutable()->make();
		$env = \Dotenv\Dotenv::create($repo, $dir ?? $_SERVER['DOCUMENT_ROOT']);
		$this->_env = $env->safeLoad();
	}

	/**
	 * Get environment variable.
	 *
	 * @param string $key Environment variable key.
	 * @param string|null $fallback Fallback value (defaults to null).
	 *
	 * @return ?string Environment variable value or fallback.
	 */
	public function get(string $key, ?string $fallback = null): ?string {
		$key = Env::$aliases[$key] ?? $key;
		return $this->_env[$key] ?? $_ENV[$key] ?? $_SERVER[$key] ?? $fallback;
	}

	/**
	 * Register an alias key for an environment variable.
	 *
	 * @param string $alias Alias key.
	 * @param string $original Original key.
	 */
	static public function registerAlias(string $alias, string $original): void {
		Env::$aliases[$alias] = $original;
	}
}

