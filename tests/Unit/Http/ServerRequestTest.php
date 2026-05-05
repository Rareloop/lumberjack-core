<?php

namespace Rareloop\Lumberjack\Test\Http;

use Mockery;
use Rareloop\Lumberjack\Test\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Rareloop\Lumberjack\Http\ServerRequest;
use Rareloop\Psr7ServerRequestExtension\InteractsWithInput;
use Rareloop\Psr7ServerRequestExtension\InteractsWithUri;
use Laminas\Diactoros\ServerRequest as DiactorosServerRequest;
use PHPUnit\Framework\Attributes\Test;

class ServerRequestTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    #[Test]
    public function request_is_prs7_compliant(): void
    {
        $request = new ServerRequest;

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    #[Test]
    public function request_uses_extension_traits(): void
    {
        $request = new ServerRequest;

        $traits = array_keys(class_uses($request));

        $this->assertContains(InteractsWithInput::class, $traits);
        $this->assertContains(InteractsWithUri::class, $traits);
    }

    #[Test]
    public function can_create_from_a_request_instance(): void
    {
        $request = new DiactorosServerRequest([], [], '/test/123', 'GET');

        $lumberjackRequest = ServerRequest::fromRequest($request);

        $this->assertInstanceOf(ServerRequest::class, $lumberjackRequest);
    }

    #[Test]
    public function fromRequest_parses_json_requests(): void
    {
        $request = new DiactorosServerRequest([], [], '/test/123', 'POST', 'data://text/plain,{"foo": "bar"}', ['Content-Type' => 'application/json']);

        $lumberjackRequest = ServerRequest::fromRequest($request);

        $this->assertSame('bar', $lumberjackRequest->input('foo'));
    }

    #[Test]
    public function ajax_method_returns_true_when_from_ajax(): void
    {
        $request = new DiactorosServerRequest([], [], '/test/123', 'GET');
        $request = $request->withHeader('X-Requested-With', 'XMLHttpRequest');

        $lumberjackRequest = ServerRequest::fromRequest($request);

        $this->assertTrue($lumberjackRequest->ajax());
    }

    #[Test]
    public function ajax_method_returns_false_when_not_from_ajax(): void
    {
        $request = new DiactorosServerRequest([], [], '/test/123', 'GET');

        $lumberjackRequest = ServerRequest::fromRequest($request);

        $this->assertFalse($lumberjackRequest->ajax());
    }

    #[Test]
    public function getMethod_is_always_uppercase(): void
    {
        $request1 = ServerRequest::fromRequest(new DiactorosServerRequest([], [], '/test/123', 'GET'));
        $request2 = ServerRequest::fromRequest(new DiactorosServerRequest([], [], '/test/123', 'get'));

        $this->assertSame('GET', $request1->getMethod());
        $this->assertSame('GET', $request2->getMethod());
    }
}
