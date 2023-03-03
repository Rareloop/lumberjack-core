<?php

namespace Rareloop\Lumberjack\Test\Http\Responses;

use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Http\Responses\RedirectResponse;
use Rareloop\Lumberjack\Session\SessionManager;

class RedirectResponseTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function is_a_psr_response_implementation()
    {
        $response = new RedirectResponse('/another.php');

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /** @test */
    public function has_a_302_status_code_by_default()
    {
        $response = new RedirectResponse('/another.php');

        $this->assertSame(302, $response->getStatusCode());
    }

    /** @test */
    public function can_specify_a_301_status_code()
    {
        $response = new RedirectResponse('/another.php', 301);

        $this->assertSame(301, $response->getStatusCode());
    }

    /** @test */
    public function sets_the_location_header()
    {
        $response = new RedirectResponse('/another.php');

        $this->assertSame('/another.php', $response->getHeader('Location')[0]);
    }

    /** @test */
    public function can_call_with_method_to_flash_data_to_the_session()
    {
        $app = new Application;
        $session = Mockery::mock(SessionManager::class);
        $session->shouldReceive('flash')->with('key', 'value')->once();
        $session->shouldReceive('flash')->with('foo', 'bar')->once();
        $app->bind('session', $session);

        $response = new RedirectResponse('/another.php');
        // Make sure we get an instance of RedirectResponse back from 'with'
        $this->assertSame($response, $response->with('key', 'value')->with('foo', 'bar'));
    }

    /** @test */
    public function can_call_with_method_to_flash_data_to_the_session_using_an_array()
    {
        $app = new Application;
        $session = Mockery::mock(SessionManager::class);
        $session->shouldReceive('flash')->with([
            'key' => 'value',
            'foo' => 'bar',
        ])->once();
        $app->bind('session', $session);

        $response = new RedirectResponse('/another.php');
        $response->with([
            'key' => 'value',
            'foo' => 'bar',
        ]);
    }
}
