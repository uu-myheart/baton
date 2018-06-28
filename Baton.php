<?php

namespace Curia\Baton;

use Psr\Http\Message\ServerRequestInterface;
use SplQueue;
use Exception;

class Baton
{
    protected $container;

	protected $queue;

	protected $method = 'process';

	protected $passable;

    public function __construct($container = null)
    {
        $this->container = $container;
    }

    public function send($passable)
    {
        $this->passable = $passable;

        return $this;
    }

	public function through(iterable $strainers)
	{
		if (count($strainers) === 0) {
			throw new Exception("[middlewares] must not be empty", 1);
		}

		$queue = new SplQueue;

		foreach ($strainers as $strainer) {
		    if (is_string($strainer)) {
                $strainer = $this->container->get($strainer);
            }

			$queue->enqueue($strainer);
		}

		$this->queue = $queue;

		return $this;
	}

	public function handle($request)
	{
        $strainer = $this->queue->dequeue();

		if (method_exists($strainer, $this->method)) {
			return $strainer->{$this->method}($request, $this);
		}

		return $strainer($request, $this);
	}

    public function via($method)
    {
        $this->method = $method;

        return $this;
	}

    public function then(Callable $destination)
    {
        $this->queue->enqueue($destination);

        return $this->handle($this->passable);
	}

    public function __invoke($request)
    {
        return $this->handle($request);
	}
}
