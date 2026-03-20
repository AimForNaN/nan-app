<?php

namespace NaN\App\Controller\Traits;

use NaN\Http\Response;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

trait GetControllerTrait {
	public function get(): PsrResponseInterface {
		return new Response(204);
	}
}
