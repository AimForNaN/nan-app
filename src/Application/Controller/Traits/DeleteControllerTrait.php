<?php

namespace NaN\Application\Controller\Traits;

use NaN\Http\Response;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

trait DeleteControllerTrait {
	public function delete(): PsrResponseInterface {
		return new Response(501);
	}
}
