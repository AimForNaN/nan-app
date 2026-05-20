<?php

namespace NaN\Application\Controller\Traits;

use NaN\Application\Controller\Interfaces\{
	ConnectControllerInterface,
	DeleteControllerInterface,
	OptionsControllerInterface,
	PatchControllerInterface,
	PostControllerInterface,
	PutControllerInterface,
	TraceControllerInterface,
};
use NaN\Http\{
	Response,
	Streams\TempStream,
};
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
		return $this->get(...$args)->withBody(new TempStream());
	}
}
