<?php

namespace NaN\Application\Interfaces;

interface ApplicationInterface extends \Psr\Http\Server\RequestHandlerInterface {
	public function run(): void;
}
