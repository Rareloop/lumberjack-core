<?php

namespace Rareloop\Lumberjack\Test\Http\Responses;

use Hamcrest\Arrays\IsArrayContainingKeyValuePair;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Http\Responses\TimberResponse;
use Timber\Timber;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * The above is required as we're using alias mocks which persist between tests
 * https://laracasts.com/discuss/channels/testing/mocking-a-class-persists-over-tests/replies/103075
 */
class TimberResponseTest extends TestCase
{
    /** @test */
    public function constructor_calls_timber_compile()
    {
        $context = [
            'foo' => 'bar',
        ];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('compile')
            ->with('template.twig', IsArrayContainingKeyValuePair::hasKeyValuePair('foo', 'bar'))
            ->once()
            ->andReturn('testing123');

        $response = new TimberResponse('template.twig', $context, 123);

        $this->assertSame(123, $response->getStatusCode());
        $this->assertSame('testing123', $response->getBody()->__toString());
    }

    /** @test */
    public function can_set_headers()
    {
        $headers = [
            'X-Test-Header' => 'testing',
        ];

        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('compile')->once()->andReturn('testing123');

        $response = new TimberResponse('template.twig', [], 200, $headers);

        $headers = $response->getHeaders();

        $this->assertNotNull($headers['X-Test-Header']);
        $this->assertSame('testing', $headers['X-Test-Header'][0]);
    }

    /** @test */
    public function default_status_code_is_200()
    {
        $timber = Mockery::mock('alias:' . Timber::class);
        $timber->shouldReceive('compile')->once()->andReturn('testing123');

        $response = new TimberResponse('template.twig', []);

        $this->assertSame(200, $response->getStatusCode());
    }
}
