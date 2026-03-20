<?php

namespace NaN\App\Controller\Interfaces;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface HeadControllerInterface extends GetControllerInterface {
	public function head(): PsrResponseInterface;
}
