<?php

/*
 * This file is part of the Composer NPM bridge package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Composer\NpmBridge;

use Composer\IO\NullIO;
use PHPUnit_Framework_TestCase;

class NpmBridgeFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new NpmBridgeFactory();

        $this->io = new NullIO();
        $this->vendorFinder = new NpmVendorFinder();
        $this->client = new NpmClient();
    }

    public function testCreate()
    {
        $expected = new NpmBridge($this->io, $this->vendorFinder, $this->client);

        $this->assertEquals($expected, $this->factory->create($this->io, $this->vendorFinder, $this->client));
    }
}
