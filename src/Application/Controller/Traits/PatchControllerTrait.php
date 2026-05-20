<?php

namespace NaN\Application\Controller\Traits;

use NaN\Http\Response;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

trait PatchControllerTrait {
	public function patch(): PsrResponseInterface {
		return new Response(501);
	}
}
