<?php

namespace Rareloop\Lumberjack\Test\Unit\Http;

use PHPUnit\Framework\Attributes\Test;
use Rareloop\Lumberjack\Http\TimberContext;
use Rareloop\Lumberjack\Test\TestCase;

class TimberContextTest extends TestCase
{
    #[Test]
    public function can_set_and_get_data_using_dot_notation(): void
    {
        $context = new TimberContext();

        $context->set('foo.bar', 'baz');

        $this->assertSame('baz', $context->get('foo.bar'));
        $this->assertSame(['bar' => 'baz'], $context->get('foo'));
    }

    #[Test]
    public function can_check_if_key_exists_using_dot_notation(): void
    {
        $context = new TimberContext(['foo' => ['bar' => 'baz']]);

        $this->assertTrue($context->has('foo.bar'));
        $this->assertFalse($context->has('foo.qux'));
    }

    #[Test]
    public function get_returns_default_if_key_does_not_exist(): void
    {
        $context = new TimberContext();

        $this->assertSame('default', $context->get('missing', 'default'));
    }

    #[Test]
    public function can_get_data_with_numeric_keys_using_dot_notation(): void
    {
        $context = new TimberContext([
            'posts' => [
                ['title' => 'Post 1'],
                ['title' => 'Post 2'],
            ],
            'collection' => collect([
                ['title' => 'Post 3'],
            ]),
        ]);

        $this->assertSame('Post 1', $context->get('posts.0.title'));
        $this->assertSame(['title' => 'Post 2'], $context->get('posts.1'));
        $this->assertSame('Post 3', $context->get('collection.0.title'));
    }
}
