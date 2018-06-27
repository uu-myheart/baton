<?php

namespace Curia\Baton;

use SplQueue;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class Baton implements RequestHandlerInterface
{
	protected $queue;

	public function __construct(iterable $middlewares)
	{
		//TODO
		if (count($middlewares) === 0) {
			throw new Exception("[middlewares] must not be empty", 1);
		}

		$queue = new SplQueue;

		foreach ($middlewares as $middleware) {
			$queue->enqueue($middleware);
		}

		$this->queue = $queue;
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$middleware = $this->queue->dequeue();

		if ($middleware instanceof MiddlewareInterface) {
			return $middleware->process($request, $this);
		}

		return $middleware($request, $this);
	}
}
