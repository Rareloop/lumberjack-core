<?php

namespace Rareloop\Lumberjack\Test\Http;

use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Http\ResponseEmitter;
use Laminas\Diactoros\Response\TextResponse;
use phpmock\MockBuilder;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ResponseEmitterTest extends TestCase
{
    /** @test */
    public function emit_should_echo_body_when_headers_sent()
    {
        $builder = new MockBuilder();
        $builder->setNamespace('Rareloop\Lumberjack\Http')
                ->setName('headers_sent')
                ->setFunction(function () {
                    return true;
                });
        $mock = $builder->build();
        $mock->enable();

        $response = new TextResponse('Hello World');
        $emitter = new ResponseEmitter();

        ob_start();
        $emitter->emit($response);
        $output = ob_get_clean();

        $this->assertSame('Hello World', $output);

        $mock->disable();
    }

    /** @test */
    public function emit_should_use_sapi_emitter_when_headers_not_sent()
    {
        $builder = new MockBuilder();
        $builder->setNamespace('Rareloop\Lumberjack\Http')
                ->setName('headers_sent')
                ->setFunction(function () {
                    return false;
                });
        $mock = $builder->build();
        $mock->enable();

        // SapiEmitter uses the global header() function. 
        // We mock it in the SapiEmitter's namespace to prevent it from actually sending headers
        $headerBuilder = new MockBuilder();
        $headerBuilder->setNamespace('Laminas\HttpHandlerRunner\Emitter')
                ->setName('header')
                ->setFunction(function () {
                });
        $headerMock = $headerBuilder->build();
        $headerMock->enable();

        // SapiEmitterTrait checks for namespaced headers_sent
        $emitterHeadersSentBuilder = new MockBuilder();
        $emitterHeadersSentBuilder->setNamespace('Laminas\HttpHandlerRunner\Emitter')
                ->setName('headers_sent')
                ->setFunction(function () {
                    return false;
                });
        $emitterHeadersSentMock = $emitterHeadersSentBuilder->build();
        $emitterHeadersSentMock->enable();

        $response = new TextResponse('Hello World');
        $emitter = new ResponseEmitter();

        ob_start();
        $emitter->emit($response);
        $output = ob_get_clean();

        // SapiEmitter echoes the body, so we should see 'Hello World'
        $this->assertSame('Hello World', $output);

        $mock->disable();
        $headerMock->disable();
        $emitterHeadersSentMock->disable();
    }
}
