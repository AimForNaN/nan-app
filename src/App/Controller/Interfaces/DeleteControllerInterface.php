<?php

namespace NaN\App\Controller\Interfaces;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface DeleteControllerInterface extends ControllerInterface {
	public function delete(): PsrResponseInterface;
}
