<?php

/**
 * Platine Request Handler
 *
 * Platine Request Handler is the implementation of PSR 15
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Request Handler
 * Copyright (c) 2020 Evgeniy Zyubin
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file RequestHandler.php
 *
 *  The RequestHandler class
 *
 *  @package    Platine\Http\Handler
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Http\Handler;

use InvalidArgumentException;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;

class RequestHandler implements RequestHandlerInterface
{

    /**
     * The list of middlewares
     * @var MiddlewareInterface[]
     */
    protected array $middlewares = [];

    /**
     * Create new instance of RequestHandler
     * @param MiddlewareInterface[] $middlewares the default
     * list of middlewares
     */
    public function __construct(array $middlewares = [])
    {
        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Middleware must be an instance of [%s]',
                    MiddlewareInterface::class
                ));
            }
            $this->middlewares[] = $middleware;
        }
    }

    /**
     * Add new middleware in the list
     * @param  MiddlewareInterface $middleware
     * @return self
     */
    public function use(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = clone $this;

        if (key($handler->middlewares) === null) {
            return new Response(404);
        }

        $middleware = current($handler->middlewares);
        next($handler->middlewares);

        return $middleware->process($request, $handler);
    }
}
