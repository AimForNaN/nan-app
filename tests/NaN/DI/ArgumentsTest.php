<?php

use NaN\DI\{
	Arguments,
	Container,
};

describe('Dependency Injection: Arguments', function () {
	test('Basic resolution', function () {
		$container = new Container();
		$callable = function (int $test1, string $test2) {};
		$arguments = Arguments::fromCallable($callable);
		$resolved = $arguments->resolve(['test1' => 1, 'test2' => ''], $container);

		expect($arguments)
			->toHaveCount(2)
			->and($resolved)
				->toHaveCount(2)
		;
	});
});
