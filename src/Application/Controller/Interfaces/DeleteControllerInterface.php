<?php

namespace NaN\Application\Controller\Interfaces;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface DeleteControllerInterface extends ControllerInterface {
	public function delete(): PsrResponseInterface;
}
