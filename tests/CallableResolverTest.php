<?php

declare(strict_types=1);

namespace Platine\Test\Http\Handler;

use Platine\Container\Container;
use Platine\Dev\PlatineTestCase;
use Platine\Http\Handler\CallableResolver;
use Platine\Http\Handler\Exception\CallableResolverException;
use Platine\Http\Handler\MiddlewareInterface;
use Platine\Http\Handler\RequestHandler;
use Platine\Http\Response;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Test\Fixture\CallableResolverArrayCallback;
use Platine\Test\Fixture\CallableResolverMiddlewareInstance;
use Platine\Test\Fixture\CallableResolverRequestHandlerInstance;
use stdClass;

/**
 * CallableResolver class tests
 *
 * @group core
 * @group middleware
 */
class CallableResolverTest extends PlatineTestCase
{

    public function testConstructor(): void
    {
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new CallableResolver($container);

        $rr = $this->getPrivateProtectedAttribute(CallableResolver::class, 'container');

        $this->assertEquals($container, $rr->getValue($resolver));
    }

    public function testResolveUsingMiddlewareInstance(): void
    {
        $middleware = new CallableResolverMiddlewareInstance();

        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new CallableResolver($container);

        $m = $resolver->resolve($middleware);

        $this->assertEquals($m, $middleware);

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $resp = $m->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(CallableResolverMiddlewareInstance::class, $resp->getBody());
    }

    public function testResolveUsingRequestHandlerInstance(): void
    {
        $handler = new CallableResolverRequestHandlerInstance();
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new CallableResolver($container);

        $h = $resolver->resolve($handler);

        $this->assertInstanceOf(MiddlewareInterface::class, $h);

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $resp = $h->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(CallableResolverRequestHandlerInstance::class, $resp->getBody());
    }

    public function testResolveUsingCallback(): void
    {
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $resolver = new CallableResolver($container);
        //Using Closure callback
        $h = $resolver->resolve(function () {
            return new Response();
        });

        $this->assertInstanceOf(MiddlewareInterface::class, $h);

        $resp = $h->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());

        //Using array callback
        $h = $resolver->resolve(sprintf('%s@%s', CallableResolverArrayCallback::class, 'create'));

        $this->assertInstanceOf(MiddlewareInterface::class, $h);

        $resp = $h->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(CallableResolverArrayCallback::class, $resp->getBody());
    }

    public function testResolveUsingCallbackNoResponseReturned(): void
    {
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new CallableResolver($container);

        $h = $resolver->resolve(function () {
        });

        $this->assertInstanceOf(MiddlewareInterface::class, $h);

        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $this->expectException(CallableResolverException::class);
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

        $resolver = new CallableResolver($container);

        $h = $resolver->resolve('not_resolvable_handler');

        $this->expectException(CallableResolverException::class);

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

        $resolver = new CallableResolver($container);

        $h = $resolver->resolve(CallableResolverMiddlewareInstance::class);

        $resp = $h->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(CallableResolverMiddlewareInstance::class, $resp->getBody());
    }

    public function testResolveUsingStringRequestHandlerClass(): void
    {
        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $requestHandler = $this->getMockBuilder(RequestHandler::class)
                ->getMock();

        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new CallableResolver($container);

        $h = $resolver->resolve(CallableResolverRequestHandlerInstance::class);

        $resp = $h->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(CallableResolverRequestHandlerInstance::class, $resp->getBody());
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
                ->will($this->returnValue(new CallableResolverRequestHandlerInstance()));

        $resolver = new CallableResolver($container);

        $h = $resolver->resolve(CallableResolverRequestHandlerInstance::class);

        $resp = $h->process($request, $requestHandler);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(CallableResolverRequestHandlerInstance::class, $resp->getBody());
    }

    public function testResolveUsingInvalidHandlerParamIsObject(): void
    {
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new CallableResolver($container);

        $this->expectException(CallableResolverException::class);
        $h = $resolver->resolve(new stdClass());
    }

    public function testResolveUsingInvalidHandlerParamIsScalar(): void
    {
        $container = $this->getMockBuilder(Container::class)
                ->getMock();

        $resolver = new CallableResolver($container);

        $this->expectException(CallableResolverException::class);
        $h = $resolver->resolve(12345);
    }
}
