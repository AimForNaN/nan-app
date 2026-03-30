<?php

namespace NaN\App\Controller\Interfaces;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface PatchControllerInterface extends ControllerInterface {
	public function patch(): PsrResponseInterface;
}
