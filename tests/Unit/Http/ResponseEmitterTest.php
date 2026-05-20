<?php

namespace Rareloop\Lumberjack\Test\Http;

use Rareloop\Lumberjack\Test\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Http\ResponseEmitter;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Mockery;
use phpmock\MockBuilder;

class ResponseEmitterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

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
    public function emit_should_use_provided_emitter_when_headers_not_sent(): void
    {
        $builder = new MockBuilder();
        $builder->setNamespace('Rareloop\Lumberjack\Http')
            ->setName('headers_sent')
            ->setFunction(function () {
                return false;
            });
        $mock = $builder->build();
        $mock->enable();

        $response = new TextResponse('Hello World');
        $innerEmitter = Mockery::mock(EmitterInterface::class);

        $innerEmitter->shouldReceive('emit')->once()->with($response)->andReturn(true);

        $emitter = new ResponseEmitter($innerEmitter);
        $emitter->emit($response);

        $mock->disable();
    }

    #[Test]
    public function emit_should_use_stacked_buffer_when_output_buffer_has_content_but_headers_not_sent(): void
    {
        $builder = new MockBuilder();
        $builder->setNamespace('Rareloop\Lumberjack\Http')
            ->setName('headers_sent')
            ->setFunction(function () {
                return false;
            });
        $mock = $builder->build();
        $mock->enable();

        $response = new TextResponse('Hello World');
        $innerEmitter = Mockery::mock(EmitterInterface::class);

        // We expect the inner emitter to be called.
        // We'll make it echo something to verify it's captured by the stacked buffer
        $innerEmitter->shouldReceive('emit')->once()->with($response)->andReturnUsing(function () {
            echo 'Hello World';
            return true;
        });

        $emitter = new ResponseEmitter($innerEmitter);

        // Start output buffering and put some content in it
        ob_start();
        echo 'Existing output';

        // We expect no exception now
        $emitter->emit($response);

        // Final output should contain both existing and new content
        $output = ob_get_clean();

        $this->assertSame('Existing outputHello World', $output);

        $mock->disable();
    }
}
