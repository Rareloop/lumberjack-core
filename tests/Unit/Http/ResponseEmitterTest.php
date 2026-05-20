<?php

namespace Rareloop\Lumberjack\Test\Http;

use Rareloop\Lumberjack\Test\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Http\ResponseEmitter;
use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use phpmock\MockBuilder;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ResponseEmitterTest extends TestCase
{
    #[Test]
    public function emit_should_echo_body_when_headers_sent(): void
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

    #[Test]
    public function emit_should_use_sapi_emitter_when_headers_not_sent(): void
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

    #[Test]
    public function emit_should_not_throw_exception_when_output_buffer_has_content_but_headers_not_sent(): void
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

        // Start output buffering and put some content in it
        ob_start();
        echo 'Existing output';

        // We expect no exception now
        ob_start();
        $emitter->emit($response);
        $output = ob_get_clean();

        // Final output should contain both existing and new content
        // The outer buffer was started before 'Existing output'
        $existing = ob_get_clean();

        $this->assertSame('Hello World', $output);
        $this->assertSame('Existing output', $existing);

        $mock->disable();
        $headerMock->disable();
        $emitterHeadersSentMock->disable();
    }
}
