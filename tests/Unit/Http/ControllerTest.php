<?php

namespace Rareloop\Lumberjack\Test\Unit\Http;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Http\Controller;
use Rareloop\Lumberjack\Http\Responses\TimberResponse;
use Rareloop\Lumberjack\Test\TestCase;
use Timber\Timber;
use Rareloop\Lumberjack\Application;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    #[Test]
    public function can_get_a_timber_response_from_the_controller(): void
    {
        new Application();
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('compile')->once()->andReturn('testing123');

        $controller = new Controller();
        $response = $controller->view('template.twig', ['foo' => 'bar'], 201, ['X-Header' => 'value']);

        $this->assertInstanceOf(TimberResponse::class, $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('value', $response->getHeader('X-Header')[0]);
    }
}
