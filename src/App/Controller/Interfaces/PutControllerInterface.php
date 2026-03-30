<?php

namespace NaN\App\Controller\Interfaces;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface PutControllerInterface extends ControllerInterface {
	public function put(): PsrResponseInterface;
}
