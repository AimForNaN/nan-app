<?php

namespace NaN\Http;

use Nette\Schema\{Processor,Schema};
use Psr\Http\Message\ServerRequestInterface as PsrServerRequestInterface;

class RequestValidator {
	public function validate(PsrServerRequestInterface $request, Schema $schema): mixed {
		$method = \strtoupper($request->getMethod());
		$data = $method === 'GET' ? $request->getQueryParams() : $request->getParsedBody();
		$processor = new Processor();

		return $processor->process($schema, $data);
	}
}
