<?php

namespace NaN\App\Controller\Interfaces;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ConnectControllerInterface extends ControllerInterface {
	public function connect(): PsrResponseInterface;
}
