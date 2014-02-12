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
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use PHPUnit_Framework_TestCase;
use Phake;

class NpmBridgePluginTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->bridgeFactory = Phake::mock('Eloquent\Composer\NpmBridge\NpmBridgeFactoryInterface');
        $this->plugin = new NpmBridgePlugin($this->bridgeFactory);

        $this->bridge = Phake::mock('Eloquent\Composer\NpmBridge\NpmBridgeInterface');
        $this->composer = new Composer;
        $this->io = new NullIO;

        Phake::when($this->bridgeFactory)->create(Phake::anyParameters())->thenReturn($this->bridge);
    }

    public function testConstructor()
    {
        $this->assertSame($this->bridgeFactory, $this->plugin->bridgeFactory());
    }

    public function testConstructorDefaults()
    {
        $this->plugin = new NpmBridgePlugin;

        $this->assertEquals(new NpmBridgeFactory, $this->plugin->bridgeFactory());
    }

    public function testActivate()
    {
        $this->assertNull($this->plugin->activate($this->composer, $this->io));
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

    public function testOnPostInstallCmd()
    {
        $this->plugin->onPostInstallCmd(new Event(ScriptEvents::POST_INSTALL_CMD, $this->composer, $this->io, true));

        Phake::inOrder(
            Phake::verify($this->bridgeFactory)->create($this->io),
            Phake::verify($this->bridge)->install($this->composer, true)
        );
    }

    public function testOnPostInstallCmdProductionMode()
    {
        $this->plugin->onPostInstallCmd(new Event(ScriptEvents::POST_INSTALL_CMD, $this->composer, $this->io, false));

        Phake::inOrder(
            Phake::verify($this->bridgeFactory)->create($this->io),
            Phake::verify($this->bridge)->install($this->composer, false)
        );
    }

    public function testOnPostUpdateCmd()
    {
        $this->plugin->onPostUpdateCmd(new Event(ScriptEvents::POST_UPDATE_CMD, $this->composer, $this->io));

        Phake::inOrder(
            Phake::verify($this->bridgeFactory)->create($this->io),
            Phake::verify($this->bridge)->update($this->composer)
        );
    }
}
