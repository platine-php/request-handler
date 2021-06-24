<?php

/**
 * Platine Request Handler
 *
 * Platine Request Handler is the implementation of PSR 15
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Request Handler
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

declare(strict_types=1);

namespace Platine\Http\Handler\Exception;

use InvalidArgumentException;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\ResponseInterface;

class MiddlewareResolverException extends InvalidArgumentException
{

    /**
     * Create the Exception instance
     * @param  mixed $handler
     * @return self
     */
    public static function create($handler): self
    {
        return new self(
            sprintf(
                'Handler %s must be an instance or name of class that implements "%s" or "%s"' .
                        ' Interface as well as a PHP callable.',
                self::convertToString($handler),
                MiddlewareInterface::class,
                RequestHandlerInterface::class
            )
        );
    }

    /**
     * @param  mixed $response
     * @return self
     */
    public static function forCallableMissingResponse($response): self
    {
        return new self(
            sprintf(
                'Callable handler must returned an instance "%s"; but received "%s"' .
                        ' Interface as well as a PHP callable.',
                ResponseInterface::class,
                self::convertToString($response)
            )
        );
    }

    /**
     * @param  string $handler
     * @return self
     */
    public static function forStringNotConvertedToInstance(string $handler): self
    {
        return new self(
            sprintf(
                'String handler %s must be a name of class or an identifier of' .
                        ' a container definition that implements "%s" or "%s" interface.',
                $handler,
                MiddlewareInterface::class,
                RequestHandlerInterface::class
            )
        );
    }

    /**
     * Convert the given argument to string
     * @param  mixed $data
     * @return string
     */
    protected static function convertToString($data): string
    {
        return is_scalar($data)
                ? (string) $data
                : (is_object($data)
                    ? get_class($data)
                    : gettype($data)
                );
    }
}
