<?php

declare(strict_types=1);

namespace Platine\Test\Fixture;

use Platine\Http\Handler\Middleware\MiddlewareInterface;
use Platine\Http\Handler\RequestHandlerInterface;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequestInterface;

class MiddlewareResolverMiddlewareInstance implements MiddlewareInterface
{

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $resp = new Response();
        $resp->getBody()->write(__CLASS__);

        return $resp;
    }
}

class MiddlewareResolverRequestHandlerInstance implements RequestHandlerInterface
{

    public function handle(
        ServerRequestInterface $request
    ): ResponseInterface {
        $resp = new Response();
        $resp->getBody()->write(__CLASS__);

        return $resp;
    }
}

class MiddlewareResolverArrayCallback
{

    public function create(
        ServerRequestInterface $request
    ): ResponseInterface {
        $resp = new Response();
        $resp->getBody()->write(__CLASS__);

        return $resp;
    }
}
