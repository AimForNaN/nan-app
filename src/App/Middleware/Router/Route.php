<?php

namespace NaN\App\Middleware\Router;

use NaN\App;
use NaN\App\Controller\Interfaces\ControllerInterface;
use NaN\DI\{
	Arguments,
	Container,
};
use NaN\Http\Response;
use Psr\Http\Message\{
	ResponseInterface as PsrResponseInterface,
	ServerRequestInterface as PsrServerRequestInterface,
};
use Psr\Http\Server\RequestHandlerInterface as PsrRequestHandlerInterface;

class Route implements \ArrayAccess, \IteratorAggregate, PsrRequestHandlerInterface {
	public function __construct(
		public readonly ?string $path = null,
		public readonly mixed $handler = null,
		protected array $children = [],
	) {
	}

	public function contains(Route $route): bool {
		foreach ($this as $child) {
			if ($child === $route) {
				return true;
			}
		}

		return false;
	}

	public function getIterator(): \Traversable {
		yield $this;

		foreach ($this->children as $route) {
			yield from $route->getIterator();
		}
	}

	public function handle(PsrServerRequestInterface $request, ?App $app = null): PsrResponseInterface {
		$pattern = new RoutePattern($this->path);
		$pattern->compile();
		$pattern->matchesRequest($request);

		$values = $pattern->getMatches();
		$handler = $this->toCallable($request, $values);

		$container = new Container([
			Route::class => $this,
			PsrServerRequestInterface::class => $request,
		]);

		if ($app) {
			$handler = \Closure::bind($handler, $container);
			$container->addDelegates($app);
		}

		return $handler();
	}

	public function insert(string $part, Route $route): self {
		$this->children[$part] = $route;
		return $this;
	}

	public function isValid(): bool {
		return \is_callable($this->handler) || \is_a($this->handler, ControllerInterface::class);
	}

	public function match(string $part): ?Route {
		if (isset($this->children[$part])) {
			return $this->children[$part];
		}

		foreach ($this->children as $path => $child) {
			// Skip what's already been tested for!
			if ($path === $part) {
				continue;
			}

			$pattern = new RoutePattern($path);
			$pattern->compile();

			if ($pattern->matches($part)) {
				return $child;
			}
		}

		return null;
	}

	public function matches(string $path): bool {
		$pattern = new RoutePattern($this->path);
		$pattern->compile();
		return $pattern->matches($path);
	}

	public function matchesRequest(PsrServerRequestInterface $request): bool {
		return $this->matches($request->getUri()->getPath());
	}

	public function offsetExists(mixed $offset): bool {
		return isset($this->children[$offset]);
	}

	public function offsetGet(mixed $offset): mixed {
		return $this->children[$offset] ?? null;
	}

	public function offsetSet(mixed $offset, mixed $value): void {
		$this->insert($offset, $value);
	}

	public function offsetUnset(mixed $offset): void {
		unset($this->children[$offset]);
	}

	public function toCallable(PsrServerRequestInterface $request, array $values = []): callable {
		$handler = $this->handler;

		if (\is_subclass_of($handler, ControllerInterface::class))  {
			$handler = new $handler();
			$allowed_methods = $handler->getAllowedMethods();
			$method = $request->getMethod();

			if ($allowed_methods[$method] ?? false) {
				return function () use ($handler, $method, $values): PsrResponseInterface {
					/** @var Container $this */
					$method = \strtolower($method);
					$callable = \Closure::fromCallable([$handler, $method]);
					$arguments = Arguments::fromCallable($callable, $values);
					return \call_user_func($callable, ...$arguments->resolve($this));
				};
			}

			return function (): PsrResponseInterface {
				return new Response(405);
			};
		}

		return function () use ($handler, $values): PsrResponseInterface {
			/** @var Container $this */
			$arguments = Arguments::fromCallable($handler, $values);
			return \call_user_func($handler, ...$arguments->resolve($this));
		};
	}

	public function toUrl(...$params) {
		$pattern = new RoutePattern($this->path);
		$pattern->compile();

		if ($pattern->hasParameters()) {
		}

		return $this->path;
	}

	public function withHandler(mixed $handler): static {
		$route = clone $this;
		$route->handler = $handler;
		return $route;
	}

	public function withPath(string $path): static {
		$route = clone $this;
		$route->path = $path;
		return $route;
	}
}
