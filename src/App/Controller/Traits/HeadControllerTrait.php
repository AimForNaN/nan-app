<?php

namespace NaN\App\Controller\Traits;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * @method PsrResponseInterface get()
 */
trait HeadControllerTrait {
	public function head(...$args): PsrResponseInterface {
		return $this->get(...$args)->withBody(Utils::streamFor(''));
	}
}
