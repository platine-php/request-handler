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
 *  @file CallableResolver.php
 *
 *  The callable resolver class
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

use Platine\Container\ContainerInterface;
use Platine\Http\Handler\Exception\CallableResolverException;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;

class CallableResolver implements CallableResolverInterface
{

    /**
     * The container instance to use to resolve handler
     * @var ContainerInterface
     */
    protected ?ContainerInterface $container;

    /**
     * Create new resolver instance
     * @param ContainerInterface|null $container
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
     * The handler must be one of:
     * - a string (class name or identifier of a container definition) or an instance
     * that implements `MiddlewareInterface` or `RequestHandlerInterface`;
     * - a callable without arguments that returns an instance of `ResponseInterface`;
     * - a callable matching signature of `MiddlewareInterface::process()`;
     *
     * @throws CallableResolverException if the handler is not valid.
     */
    public function resolve($handler): MiddlewareInterface
    {
        if ($handler instanceof MiddlewareInterface) {
            return $handler;
        }

        if ($handler instanceof RequestHandlerInterface) {
            return $this->handler($handler);
        }

        if (is_string($handler)) {
            return $this->stringHandler($handler);
        }

        if (is_callable($handler)) {
            return $this->callableHandler($handler);
        }

        throw CallableResolverException::create($handler);
    }

    /**
     * @param  callable $handler the callable handler
     * @return MiddlewareInterface
     *
     * @throws CallableResolverException
     * if the handler does not return a `ResponseInterface` instance.
     */
    protected function callableHandler(callable $handler): MiddlewareInterface
    {
        return new class ($handler) implements MiddlewareInterface {

            /**
             *
             * @var callable
             */
            private $callable;

            /**
             * @param callable $callable
             */
            public function __construct($callable)
            {
                $this->callable = $callable;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $response = ($this->callable)($request, $handler);

                if (!$response instanceof ResponseInterface) {
                    throw CallableResolverException::forCallableMissingResponse($response);
                }

                return $response;
            }
        };
    }

    /**
     * @param  RequestHandlerInterface $handler the request handler
     * @return MiddlewareInterface
     */
    protected function handler(RequestHandlerInterface $handler): MiddlewareInterface
    {
        return new class ($handler) implements MiddlewareInterface {

            private RequestHandlerInterface $handler;

            public function __construct(RequestHandlerInterface $handler)
            {
                $this->handler = $handler;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $this->handler->handle($request);
            }
        };
    }

    /**
     * @param  string $handler the string handler name
     * @return MiddlewareInterface
     *
     * @throws CallableResolverException if the handler is not valid
     */
    protected function stringHandler(string $handler): MiddlewareInterface
    {
        return new class ($handler, $this->container) implements MiddlewareInterface {

            private string $handler;
            private ?ContainerInterface $container;

            public function __construct(string $handler, ?ContainerInterface $container)
            {
                $this->handler = $handler;
                $this->container = $container;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $name = $this->handler;
                $class = $name;
                $method = null;
                $instance = null;

                //if the value is something like Namespace1\Namespace2\ClassName@Method
                if (strpos($name, '@') !== false) {
                    $parts = explode('@', $name, 2);
                    $class = $parts[0];
                    $method = $parts[1];
                }

                if ($this->container !== null && $this->container->has($class)) {
                    $instance = $this->container->get($class);
                } elseif (class_exists($class)) {
                    $instance = new $class();
                }


                if ($instance instanceof MiddlewareInterface) {
                    return $instance->process($request, $handler);
                }

                if ($instance instanceof RequestHandlerInterface) {
                    return $instance->handle($request);
                }

                if ($method !== null && method_exists($instance, $method)) {
                    return $instance->{$method}($request, $handler);
                }

                throw CallableResolverException::forStringNotConvertedToInstance($this->handler);
            }
        };
    }
}
