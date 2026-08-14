<?php

namespace NaN\Application\Controller\Interfaces;

use Psr\Http\{
	Message\ResponseInterface as PsrResponseInterface,
	Server\MiddlewareInterface as PsrMiddlewareInterface,
};

interface ControllerInterface extends PsrMiddlewareInterface {
	public function get(): PsrResponseInterface;
	public function getAllowedMethods(): array;
	public function head(): PsrResponseInterface;
}
