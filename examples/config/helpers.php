<?php

use NaN\App;
use NaN\App\TemplateEngine;
use NaN\Database\Drivers\SqlDriver;
use NaN\Env;

function app(): App {
	static $app = null;

	if (!$app) {
		$services = include(__DIR__ . '/services.php');
		$router = include(__DIR__ . '/routes.php');
		$app = new App($services);

		$app->use($router);
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

function sql(): \NaN\Database\Query\Builders\Interfaces\QueryBuilderInterface {
	static $query = null;

	if (!$query) {
		$driver = new SqlDriver();
		$query = $driver->createQueryBuilder();
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
