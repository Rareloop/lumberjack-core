<?php

namespace Rareloop\Lumberjack\Test;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Contracts\Encrypter as EncrypterContract;
use Rareloop\Lumberjack\Encrypter;
use Rareloop\Lumberjack\Session\EncryptedStore;
use Rareloop\Lumberjack\Session\FileSessionHandler;
use Rareloop\Lumberjack\Session\SessionManager;
use Rareloop\Lumberjack\Session\Store;
use Rareloop\Lumberjack\Test\Unit\Session\NullSessionHandler;
use org\bovigo\vfs\vfsStream;

class SessionManagerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private function appWithSessionDriverConfig($driver, $cookie = 'lumberjack', $encrypted = false)
    {
        $app = new Application;
        $config = new Config;

        $config->set('session.cookie', $cookie);
        $config->set('session.driver', $driver);
        $config->set('session.encrypt', $encrypted);

        $app->bind(Config::class, $config);

        return $app;
    }

    /** @test */
    public function default_driver_is_read_from_config()
    {
        $app = $this->appWithSessionDriverConfig('driver-name');

        $manager = new SessionManager($app);

        $this->assertSame('driver-name', $manager->getDefaultDriver());
    }

    /** @test */
    public function can_create_a_file_driver()
    {
        $app = $this->appWithSessionDriverConfig('file', 'lumberjack');

        $manager = new SessionManager($app);

        $this->assertInstanceOf(FileSessionHandler::class, $manager->driver()->getHandler());
        $this->assertSame('lumberjack', $manager->driver()->getName());
    }

    /** @test */
    public function file_driver_uses_path_from_config_when_present()
    {
        $rootFileSystem = vfsStream::setup('exampleDir');
        $app = $this->appWithSessionDriverConfig('file', 'lumberjack');
        $app->get(Config::class)->set('session.files', vfsStream::url('exampleDir'));

        $manager = new SessionManager($app);

        $manager->driver()->save();

        $this->assertCount(1, $rootFileSystem->getChildren());
    }

    /** @test */
    public function can_extend_list_of_drivers()
    {
        $app = new Application;
        $manager = new SessionManager($app);

        $manager->extend('test', function () {
            return new Store('name', new TestSessionHandler);
        });

        $this->assertInstanceOf(TestSessionHandler::class, $manager->driver('test')->getHandler());
    }

    /** @test */
    public function can_create_an_unencrypted_store()
    {
        $app = $app = $this->appWithSessionDriverConfig('file', 'lumberjack', $encrypted = false);
        $manager = new SessionManager($app);

        $this->assertInstanceOf(Store::class, $manager->driver());
    }

    /** @test */
    public function can_create_an_encrypted_store()
    {
        $app = $app = $this->appWithSessionDriverConfig('file', 'lumberjack', $encrypted = true);
        $app->bind(EncrypterContract::class, new Encrypter('encryption-key'));

        $manager = new SessionManager($app);

        $this->assertInstanceOf(EncryptedStore::class, $manager->driver());
    }
}

class TestSessionHandler extends NullSessionHandler {}
