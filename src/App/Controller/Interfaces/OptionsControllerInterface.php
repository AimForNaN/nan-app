<?php

namespace NaN\App\Controller\Interfaces;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface OptionsControllerInterface extends ControllerInterface {
	public function options(): PsrResponseInterface;
}
