<?php

namespace NaN\App\Controller\Traits;

use GuzzleHttp\Psr7\Utils;
use NaN\App\Controller\Interfaces\{
	ConnectControllerInterface,
	DeleteControllerInterface,
	OptionsControllerInterface,
	PatchControllerInterface,
	PostControllerInterface,
	PutControllerInterface,
	TraceControllerInterface,
};
use NaN\Http\Response;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

trait ControllerTrait {
	public function get(): PsrResponseInterface {
		return new Response(501);
	}

	public function getAllowedMethods(): array {
		$allowed_methods = [
			'GET' => 'GET',
			'HEAD' => 'HEAD',
		];

		if ($this instanceof ConnectControllerInterface) {
			$allowed_methods['CONNECT'] = 'CONNECT';
		}

		if ($this instanceof DeleteControllerInterface) {
			$allowed_methods['DELETE'] = 'DELETE';
		}

		if ($this instanceof OptionsControllerInterface) {
			$allowed_methods['OPTIONS'] = 'OPTIONS';
		}

		if ($this instanceof PatchControllerInterface) {
			$allowed_methods['PATCH'] = 'PATCH';
		}

		if ($this instanceof PostControllerInterface) {
			$allowed_methods['POST'] = 'POST';
		}

		if ($this instanceof PutControllerInterface) {
			$allowed_methods['PUT'] = 'PUT';
		}

		if ($this instanceof TraceControllerInterface) {
			$allowed_methods['TRACE'] = 'TRACE';
		}

		return $allowed_methods;
	}

	public function head(...$args): PsrResponseInterface {
		return $this->get(...$args)->withBody(Utils::streamFor(''));
	}
}
