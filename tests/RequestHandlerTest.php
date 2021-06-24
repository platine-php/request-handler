<?php

declare(strict_types=1);

namespace Platine\Test\Http;

use InvalidArgumentException;
use Platine\Http\Handler\RequestHandler;
use Platine\Http\ResponseInterface;
use Platine\Http\ServerRequest;
use Platine\Dev\PlatineTestCase;
use Platine\Test\Fixture\MiddlewareResolverMiddlewareInstance;

/**
 * RequestHandler class tests
 *
 * @group core
 * @group http
 * @group message
 */
class RequestHandlerTest extends PlatineTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testConstructor(): void
    {
        $reflection = $this->getPrivateProtectedAttribute(RequestHandler::class, 'middlewares');
        //Default
        $c = new RequestHandler();
        $this->assertCount(0, $reflection->getValue($c));

        $c = new RequestHandler(array(new MiddlewareResolverMiddlewareInstance()));
        $this->assertCount(1, $reflection->getValue($c));

        //Route Already exists
        $this->expectException(InvalidArgumentException::class);
        $c = new RequestHandler(array(new MiddlewareResolverMiddlewareInstance(), 123));
    }

    public function testUse(): void
    {
        $reflection = $this->getPrivateProtectedAttribute(RequestHandler::class, 'middlewares');
        //Default
        $c = new RequestHandler();
        $this->assertCount(0, $reflection->getValue($c));

        $c->use(new MiddlewareResolverMiddlewareInstance());
        $this->assertCount(1, $reflection->getValue($c));
    }

    public function testHandleNoMiddleware(): void
    {
        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $c = new RequestHandler();
        $resp = $c->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEmpty($resp->getBody()->getContents());
    }

    public function testHandleMiddlewareOutputResponse(): void
    {
        $request = $this->getMockBuilder(ServerRequest::class)
                ->getMock();

        $c = new RequestHandler();
        $c->use(new MiddlewareResolverMiddlewareInstance());
        $resp = $c->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $resp);
        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertEquals(MiddlewareResolverMiddlewareInstance::class, $resp->getBody());
    }
}
