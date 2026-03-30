<?php

namespace NaN\App\Controller\Interfaces;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface PostControllerInterface extends ControllerInterface {
	public function post(): PsrResponseInterface;
}
