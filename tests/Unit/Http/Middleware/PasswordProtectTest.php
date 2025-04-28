<?php

namespace Rareloop\Lumberjack\Test\Http\Middleware;

use Mockery;
use Timber\Timber;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rareloop\Lumberjack\Http\Middleware\PasswordProtected;
use Rareloop\Lumberjack\Http\Responses\TimberResponse;
use Rareloop\Lumberjack\Test\Unit\BrainMonkeyPHPUnitIntegration;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PasswordProtectTest extends TestCase
{
    use BrainMonkeyPHPUnitIntegration;

    /** @test */
    public function it_does_nothing_when_password_is_not_required()
    {
        $middleware = new PasswordProtected;

        Functions\expect('post_password_required')
            ->once()
            ->andReturn(false);

        $request = Mockery::mock(ServerRequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);
        $handler = Mockery::mock(RequestHandlerInterface::class);

        $handler->shouldReceive('handle')->once()->with($request)->andReturn($response);

        $this->assertSame($response, $middleware->process($request, $handler));
    }

    /** @test */
    public function it_does_nothing_when_the_password_twig_file_is_not_found()
    {
        $middleware = new PasswordProtected;

        Functions\expect('post_password_required')
            ->once()
            ->andReturn(true);

        $timber = \Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_post')->once();
        $timber->shouldReceive('compile')
            ->withArgs(function ($template) {
                return $template === 'single-password.twig';
            })
            ->once()
            ->andReturn(false);

        $timber->shouldReceive('context')
            ->once()
            ->andReturn([]);

        $request = Mockery::mock(ServerRequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);
        $handler = Mockery::mock(RequestHandlerInterface::class);

        $handler->shouldReceive('handle')->once()->with($request)->andReturn($response);

        $this->assertSame($response, $middleware->process($request, $handler));
    }

    /** @test */
    public function it_renders_the_password_template_when_needed()
    {
        Functions\expect('post_password_required')
            ->once()
            ->andReturn(true);

        $request = Mockery::mock(ServerRequestInterface::class);
        $handler = Mockery::mock(RequestHandlerInterface::class);

        $timber = \Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('get_post')->once();
        $timber->shouldReceive('compile')
            ->withArgs(function ($template) {
                return $template === 'single-password.twig';
            })
            ->once()
            ->andReturn('testing123');

        $timber->shouldReceive('context')
            ->once()
            ->andReturn([]);

        $handler->shouldReceive('handle')->never();

        $middleware = new PasswordProtected;
        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(TimberResponse::class, $response);
        $this->assertSame('testing123', $response->getBody()->getContents());
        $this->assertTrue(Filters\applied('lumberjack/password_protect_template') > 0);
    }
}
