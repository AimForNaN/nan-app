<?php

namespace NaN\Http;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response extends \GuzzleHttp\Psr7\Response {
	public function __construct(int $status = 200, array $headers = [], $body = null, ?string $version = null, ?string $reason = null) {
		if (!$version) {
			[, $version] = static::parseServerProtocol();
		}

		parent::__construct($status, $headers, $body, $version, $reason);
	}

	static public function json(mixed $data): PsrResponseInterface {
		return new static(200, [
			'Content-Type' => 'application/json',
		], \json_encode($data));
	}

	static protected function parseServerProtocol(): array {
		static $parsed = null;

		if ($parsed === null) {
			$parsed = \explode('/', $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1');
		}

		return $parsed;
	}

	static public function redirect(string $path, int $status = 302): PsrResponseInterface {
		if ($status < 300 || $status > 399) {
			$status = 302;
		}

		return new static($status, [
			'Location' => $path,
		]);
	}

	static public function send(PsrResponseInterface $rsp): void {
		static::sendHeaders($rsp);

		if ($rsp->getStatusCode() !== 204) {
			static::sendBody($rsp);
		}
	}

	static public function sendBody(PsrResponseInterface $rsp): void {
		$out = Utils::streamFor(fopen('php://output', 'w'));
		$content = (string)$rsp->getBody();
		$out->write($content);
	}

	static public function sendHeaders(PsrResponseInterface $rsp): void {
		[$protocol] = static::parseServerProtocol();
		$version = $rsp->getProtocolVersion();
		$status = $rsp->getStatusCode();
		$phrase = $rsp->getReasonPhrase();
		\header("{$protocol}/{$version} {$status} {$phrase}");

		$headers = $rsp->getHeaders();
		foreach ($headers as $name => $value) {
			$value = \implode(';', $value);
			\header("{$name}: {$value}");
		}
	}
}
