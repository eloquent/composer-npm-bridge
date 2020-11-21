<?php

namespace Eloquent\Composer\NpmBridge;

use Composer\IO\NullIO;
use PHPUnit\Framework\TestCase;

class NpmBridgeFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        $this->factory = NpmBridgeFactory::create();

        $this->io = new NullIO();
    }

    public function testCreateBridge()
    {
        $expected = new NpmBridge($this->io, new NpmVendorFinder(), NpmClient::create());

        $this->assertEquals($expected, $this->factory->createBridge($this->io));
    }
}
