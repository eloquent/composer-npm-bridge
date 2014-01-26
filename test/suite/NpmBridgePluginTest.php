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

use PHPUnit_Framework_TestCase;

class NpmBridgePluginTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->plugin = new NpmBridgePlugin;
    }

    public function testConstructor()
    {
        $this->assertTrue(true);
    }
}
