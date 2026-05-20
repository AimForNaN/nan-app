<?php

namespace NaN\Application\Controller\Traits;

use NaN\Http\Response;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

trait ConnectControllerTrait {
	public function connect(): PsrResponseInterface {
		return new Response(501);
	}
}
