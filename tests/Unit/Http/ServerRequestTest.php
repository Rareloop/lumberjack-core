<?php

namespace Rareloop\Lumberjack\Test\Http;

use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Rareloop\Lumberjack\Http\ServerRequest;
use Rareloop\Psr7ServerRequestExtension\InteractsWithInput;
use Rareloop\Psr7ServerRequestExtension\InteractsWithUri;
use Zend\Diactoros\ServerRequest as DiactorosServerRequest;

class ServerRequestTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function request_is_prs7_compliant()
    {
        $request = new ServerRequest;

        $this->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    /** @test */
    public function request_uses_extension_traits()
    {
        $request = new ServerRequest;

        $traits = array_keys(class_uses($request));

        $this->assertContains(InteractsWithInput::class, $traits);
        $this->assertContains(InteractsWithUri::class, $traits);
    }

    /** @test */
    public function can_create_from_a_request_instance()
    {
        $request = new DiactorosServerRequest([], [], '/test/123', 'GET');

        $lumberjackRequest = ServerRequest::fromRequest($request);

        $this->assertInstanceOf(ServerRequest::class, $lumberjackRequest);
    }
}
