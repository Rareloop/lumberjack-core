<?php

namespace Rareloop\Lumberjack\Test;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Session\FileSessionHandler;
use Rareloop\Lumberjack\Session\Store;
use org\bovigo\vfs\vfsStream;

class FileSessionHandlerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $rootFileSystem;

    public function setUp()
    {
        $this->rootFileSystem = vfsStream::setup('exampleDir');
    }

    /** @test */
    public function open_returns_true()
    {
        $handler = new FileSessionHandler(vfsStream::url('exampleDir'));

        $this->assertTrue($handler->open('save-path', 'session-name'));
    }

    /** @test */
    public function close_returns_true()
    {
        $handler = new FileSessionHandler(vfsStream::url('exampleDir'));

        $this->assertTrue($handler->close('save-path', 'session-name'));
    }

    /** @test */
    public function write_creates_a_file_to_disk()
    {
        $handler = new FileSessionHandler(vfsStream::url('exampleDir'));

        $response = $handler->write('12345', 'abc');

        $this->assertTrue($response);
        $this->assertTrue($this->rootFileSystem->hasChild('lumberjack_session_12345'));
        $this->assertSame('abc', file_get_contents(vfsStream::url('exampleDir/lumberjack_session_12345')));
    }

    /** @test */
    public function read_gets_data_from_disk_when_file_exists()
    {
        $handler = new FileSessionHandler(vfsStream::url('exampleDir'));
        file_put_contents(vfsStream::url('exampleDir/lumberjack_session_12345'), 'abc');

        $response = $handler->read('12345');

        $this->assertSame('abc', $response);
    }

    /** @test */
    public function read_returns_empty_string_when_file_does_not_exist()
    {
        $handler = new FileSessionHandler(vfsStream::url('exampleDir'));

        $response = $handler->read('12345');

        $this->assertSame('', $response);
    }

    /** @test */
    public function destroy_removes_file_from_disk_when_file_exists()
    {
        $handler = new FileSessionHandler(vfsStream::url('exampleDir'));
        file_put_contents(vfsStream::url('exampleDir/lumberjack_session_12345'), 'abc');

        $response = $handler->destroy('12345');

        $this->assertTrue($response);
        $this->assertFalse($this->rootFileSystem->hasChild('lumberjack_session_12345'));
    }

    /** @test */
    public function destroy_returns_true_when_file_does_not_exist()
    {
        $handler = new FileSessionHandler(vfsStream::url('exampleDir'));

        $response = $handler->destroy('12345');

        $this->assertTrue($response);
        $this->assertFalse($this->rootFileSystem->hasChild('lumberjack_session_12345'));
    }

    /** @test */
    public function gc_does_not_remove_files_that_are_not_older_than_the_lifetime()
    {
        $handler = new FileSessionHandler(vfsStream::url('exampleDir'));
        file_put_contents(vfsStream::url('exampleDir/lumberjack_session_12345'), 'abc');

        sleep(1);

        $response = $handler->gc(2);

        $this->assertTrue($response);
        $this->assertTrue($this->rootFileSystem->hasChild('lumberjack_session_12345'));
    }

    // TODO: add test gc_removes_files_that_are_older_than_the_lifetime()
    // vsfStream doesn't support glob so we either need to change our implementation or
    // figure out a way to get it working in the tests
}
