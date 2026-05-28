<?php

namespace NaN\Application\Interfaces;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Server\MiddlewareInterface as PsrMiddlewareInterface;

interface ApplicationInterface extends \Psr\Http\Server\RequestHandlerInterface {
	public function run(): void;
}
