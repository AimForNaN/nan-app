<?php

namespace NaN\Application\Controller\Traits;

use NaN\Http\Response;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

trait PutControllerTrait {
	public function put(): PsrResponseInterface {
		return new Response(501);
	}
}
