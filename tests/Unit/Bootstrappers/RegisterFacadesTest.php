<?php

namespace Rareloop\Lumberjack\Test\Bootstrappers;

use Rareloop\Lumberjack\Test\TestCase;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\RegisterFacades;
use Rareloop\Lumberjack\FacadeFactory;
use PHPUnit\Framework\Attributes\Test;

class RegisterFacadesTest extends TestCase
{
    #[Test]
    public function boots_all_registered_providers()
    {
        $app = new Application;

        $registerFacadesBootstrapper = new RegisterFacades;
        $registerFacadesBootstrapper->bootstrap($app);

        $this->assertSame($app, FacadeFactory::getContainer());
    }
}
