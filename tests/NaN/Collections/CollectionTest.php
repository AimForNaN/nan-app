<?php

use NaN\Collections\Collection;

describe('Collection', function () {
	test('Count', function () {
		$collection = new Collection(1,2,3);
		expect($collection)->toHaveCount(3);
	});

	test('Map', function () {
		$collection = new Collection(1,2,3);
		$mappings = $collection->map(fn($item) => (string)$item);
		$mappings = [...$mappings];

		expect($mappings)
			->toHaveCount(3)
			->and($mappings)
				->toBe(['1', '2', '3'])
		;
	});

	test('Splat operator', function () {
		$collection = new Collection(1,2,3);
		expect([...$collection])->toBe([1,2,3]);
	});
});
