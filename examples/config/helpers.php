<?php

use NaN\App;
use NaN\App\TemplateEngine;
use NaN\Database\Query\Builders\Interfaces\QueryBuilderInterface;
use NaN\Database\Sql\Drivers\SqlDriver;
use NaN\Env;

function app(): App {
	static $app = null;

	if (!$app) {
		$services = include(__DIR__ . '/services.php');
		$middleware = include(__DIR__ . '/middleware.php');
		$app = new App($services, $middleware);
	}

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
