<?php

namespace Curia\Baton;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class Baton implements RequestHandlerInterface
{
	protected $queue;

	public function __construct(iterable $middlewares)
	{
		$queue = new \SplQueue;

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
