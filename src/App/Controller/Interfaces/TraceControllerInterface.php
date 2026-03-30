<?php

namespace NaN\App\Controller\Interfaces;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface TraceControllerInterface extends ControllerInterface {
	public function trace(): PsrResponseInterface;
}
