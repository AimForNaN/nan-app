<?php

use NaN\Collections\Collection;

class CollectionBench {
	public function benchArray(): void {
		$collection = [];

		for ($i = 0; $i < 1000; $i++) {
			$collection[] = 0;
		}
	}

	/**
	 * @Warmup(1)
	 */
	public function benchCollection(): void {
		$collection = new Collection(...range(1, 1000));
	}
}
