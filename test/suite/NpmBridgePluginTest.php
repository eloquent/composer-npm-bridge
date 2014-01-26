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

use Composer\Composer;
use Composer\IO\NullIO;
use Composer\Script\ScriptEvents;
use PHPUnit_Framework_TestCase;

class NpmBridgePluginTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->plugin = new NpmBridgePlugin;
    }

    public function testActivate()
    {
        $composer = new Composer;
        $io = new NullIO;

        $this->assertNull($this->plugin->activate($composer, $io));
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(
                ScriptEvents::POST_INSTALL_CMD => 'onPostInstallCmd',
                ScriptEvents::POST_UPDATE_CMD => 'onPostUpdateCmd',
            ),
            $this->plugin->getSubscribedEvents()
        );
    }
}
