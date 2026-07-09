<?php

use NaN\Application\Interfaces\ApplicationInterface;
use NaN\Application\NativeApplication as App;
use NaN\Database\Query\Builders\Interfaces\QueryBuilderInterface;
use NaN\Database\Sql\Drivers\SqlDriver;
use NaN\Env;
use NaN\Template\TemplateEngine;

function app(): ApplicationInterface {
	static $app = new App(
		include(__DIR__ . '/services.php'),
		include(__DIR__ . '/middleware.php'),
	);

	return $app;
}

/**
 * @throws Exception
 */
function db(): \NaN\Database\Interfaces\ConnectionInterface {
	static $db = null;

	if (!$db) {
		$driver = new SqlDriver();
		$db = $driver->createConnection([
			'driver' => 'sqlite',
			'sqlite' => ':memory:',
		]);
	}

	return $db;
}

function dbg(mixed $msg): void {
	\NaN\Debug::log($msg);
}

function env(string $key, mixed $fallback = null): ?string {
	if (!Env::isLoaded()) {
		Env::load();
	}

	return Env::get($key, $fallback);
}

/**
 * @throws Exception
 */
function sql(string $sql = '', array $binding = []): QueryBuilderInterface|\PDOStatement|false {
	static $query = null;

	if (!$query) {
		$driver = new SqlDriver();
		$query = $driver->createQueryBuilder();
	}

	if (!empty($sql)) {
		return db()->raw($sql, $binding);
	}

	return $query;
}

function tpl(): TemplateEngine {
	static $tpl = null;

	if (!$tpl) {
		$tpl = new TemplateEngine($_SERVER['DOCUMENT_ROOT'] . '/views/', 'tpl.php');
	}

	return $tpl;
}
