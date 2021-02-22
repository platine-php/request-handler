<?php

declare(strict_types=1);

namespace Platine\Test\Http\Handler\Middleware;

use Platine\Container\Container;
use Platine\Http\Handler\Middleware\Exception\MiddlewareResolverException;
use Platine\Http\Handler\Middleware\MiddlewareInterface;
use Platine\Http\Handler\Middleware\MiddlewareResolver;
use Platine\Http\Handler\RequestHandler;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\PlatineTestCase;
use Platine\Test\Fixture\MiddlewareResolverArrayCallback;
use Platine\Test\Fixture\MiddlewareResolverMiddlewareInstance;
use Platine\Test\Fixture\MiddlewareResolverRequestHandlerInstance;
use stdClass;

/**
 * MiddlewareResolver class tests
 *
 * @group core
 * @group middleware
 */
class MiddlewareResolverTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);

        $rr = $this->getPrivateProtectedAttribute(MiddlewareResolver::class, 'container');

        $this->assertEquals($container, $rr->getValue($resolver));
    }

    public function testResolveUsingMiddlewareInstance(): void
    {
        $middleware = new MiddlewareResolverMiddlewareInstance();

        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);

        $m = $resolver->resolve($middleware);

        $this->assertEquals($m, $middleware);

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $resp = $m->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(MiddlewareResolverMiddlewareInstance::class, $resp->getBody());
    }

    public function testResolveUsingRequestHandlerInstance(): void
    {
        $handler = new MiddlewareResolverRequestHandlerInstance();
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);

        $h = $resolver->resolve($handler);

        $this->assertInstanceOf(MiddlewareInterface::class, $h);

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $resp = $h->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(MiddlewareResolverRequestHandlerInstance::class, $resp->getBody());
    }

    public function testResolveUsingCallback(): void
    {
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);
        //Using Closure callback
        $h = $resolver->resolve(function () {
            return new Response();
        });

        $this->assertInstanceOf(MiddlewareInterface::class, $h);

        $resp = $h->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());

        //Using array callback
        $h = $resolver->resolve(sprintf('%s@%s', MiddlewareResolverArrayCallback::class, 'create'));

        $this->assertInstanceOf(MiddlewareInterface::class, $h);

        $resp = $h->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(MiddlewareResolverArrayCallback::class, $resp->getBody());
    }

    public function testResolveUsingCallbackNoResponseReturned(): void
    {
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);

        $h = $resolver->resolve(function () {
        });

        $this->assertInstanceOf(MiddlewareInterface::class, $h);

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $this->expectException(MiddlewareResolverException::class);
        $resp = $h->process($request, $requestHandler);
    }

    public function testResolveUsingStringNotResolvable(): void
    {
        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);

        $h = $resolver->resolve('not_resolvable_handler');

        $this->expectException(MiddlewareResolverException::class);

        $resp = $h->process($request, $requestHandler);
    }

    public function testResolveUsingStringMiddlewareClass(): void
    {
        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);

        $h = $resolver->resolve(MiddlewareResolverMiddlewareInstance::class);

        $resp = $h->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(MiddlewareResolverMiddlewareInstance::class, $resp->getBody());
    }

    public function testResolveUsingStringRequestHandlerClass(): void
    {
        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);

        $h = $resolver->resolve(MiddlewareResolverRequestHandlerInstance::class);

        $resp = $h->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(MiddlewareResolverRequestHandlerInstance::class, $resp->getBody());
    }

    public function testResolveUsingStringContainer(): void
    {
        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $container = $this->getMockBuilder(Container::class)
                ->getMock();
        $container->expects($this->any())
                ->method('has')
                ->will($this->returnValue(true));

        $container->expects($this->any())
                ->method('get')
                ->will($this->returnValue(new MiddlewareResolverRequestHandlerInstance()));

        $resolver = new MiddlewareResolver($container);

        $h = $resolver->resolve(MiddlewareResolverRequestHandlerInstance::class);

        $resp = $h->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(MiddlewareResolverRequestHandlerInstance::class, $resp->getBody());
    }

    public function testResolveUsingInvalidHandlerParamIsObject(): void
    {
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);

        $this->expectException(MiddlewareResolverException::class);
        $h = $resolver->resolve(new stdClass());
    }

    public function testResolveUsingInvalidHandlerParamIsScalar(): void
    {
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new MiddlewareResolver($container);

        $this->expectException(MiddlewareResolverException::class);
        $h = $resolver->resolve(12345);
    }
}
