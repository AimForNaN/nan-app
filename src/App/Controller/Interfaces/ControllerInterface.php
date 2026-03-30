<?php

namespace NaN\App\Controller\Interfaces;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ControllerInterface {
	public function get(): PsrResponseInterface;
	public function getAllowedMethods(): array;
	public function head(): PsrResponseInterface;
}
