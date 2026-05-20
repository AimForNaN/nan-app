<?php

namespace NaN\Application\Controller\Interfaces;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface PostControllerInterface extends ControllerInterface {
	public function post(): PsrResponseInterface;
}
