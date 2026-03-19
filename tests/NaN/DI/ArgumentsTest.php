<?php

use NaN\DI\{
	Arguments,
	Container,
};

describe('Dependency Injection: Arguments', function () {
	test('Basic resolution', function () {
		$container = new Container();
		$callable = function (int $test1, string $test2) {};
		$arguments = Arguments::fromCallable($callable, [1, '']);
		expect($arguments)
			->toHaveCount(2)
			->and($arguments->resolve($container))
				->toHaveCount(2)
		;
	});

	test('Iteration', function () {
		$callable = function (int $test1, string $test2) {};
		$arguments = Arguments::fromCallable($callable);
		expect($arguments)
			->toHaveCount(2)
			->and([...$arguments])
				->toHaveCount(2)
		;
	});
});
