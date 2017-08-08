<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Blast\Facades\FacadeFactory;
use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\RegisterFacades;

class RegisterFacadesTest extends TestCase
{
    /** @test */
    public function boots_all_registered_providers()
    {
        $app = new Application;

        $registerFacadesBootstrapper = new RegisterFacades;
        $registerFacadesBootstrapper->bootstrap($app);

        $this->assertSame($app, FacadeFactory::getContainer());
    }
}
