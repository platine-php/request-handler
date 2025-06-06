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
 *  @file MiddlewareResolverInterface.php
 *
 *  The callable resolver interface
 *
 *  @package    Platine\Http\Handler
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Http\Handler;

/**
 * @class MiddlewareResolverInterface
 * @package Platine\Http\Handler
 */
interface MiddlewareResolverInterface
{
    /**
     * Resolve the given callable by converting it to middleware instance.
     *
     * If the handler cannot be resolved or is invalid, an exception may be thrown.
     *
     * @param  string|MiddlewareInterface|RequestHandlerInterface|callable $handler
     * @return MiddlewareInterface
     */
    public function resolve(
        string|MiddlewareInterface|RequestHandlerInterface|callable $handler
    ): MiddlewareInterface;
}
