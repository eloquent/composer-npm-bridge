<?php

/*
 * This file is part of the Typhoon package.
 *
 * Copyright © 2012 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Composer\NPMBridge;

use Composer\Script\ScriptEvents;
use Phake;
use PHPUnit_Framework_TestCase;

class NPMBridgeTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->_eventPostInstall = Phake::mock('Composer\Script\Event');
        Phake::when($this->_eventPostInstall)
            ->getName()
            ->thenReturn(ScriptEvents::POST_INSTALL_CMD)
        ;
        $this->_eventPostUpdate = Phake::mock('Composer\Script\Event');
        Phake::when($this->_eventPostUpdate)
            ->getName()
            ->thenReturn(ScriptEvents::POST_UPDATE_CMD)
        ;
        $this->_eventUnknown = Phake::mock('Composer\Script\Event');
        Phake::when($this->_eventUnknown)
            ->getName()
            ->thenReturn('foo')
        ;
    }

    public function testGet()
    {
        $instance = new NPMBridge;
        $alternative = NPMBridge::get();

        $this->assertSame($instance, NPMBridge::get($instance));
        $this->assertInstanceOf(__NAMESPACE__.'\NPMBridge', $alternative);
        $this->assertNotSame($instance, $alternative);
    }

    public function testHandlePostInstall()
    {
        $instance = Phake::mock(__NAMESPACE__.'\NPMBridge');
        NPMBridge::handle($this->_eventPostInstall, $instance);

        Phake::verify($instance)->postInstall(
            $this->identicalTo($this->_eventPostInstall)
        );
        Phake::verify($instance, Phake::never())->postUpdate(
            Phake::anyParameters()
        );
    }

    public function testHandlePostUpdate()
    {
        $instance = Phake::mock(__NAMESPACE__.'\NPMBridge');
        NPMBridge::handle($this->_eventPostUpdate, $instance);

        Phake::verify($instance)->postUpdate(
            $this->identicalTo($this->_eventPostUpdate)
        );
        Phake::verify($instance, Phake::never())->postInstall(
            Phake::anyParameters()
        );
    }

    public function testHandlePostUnknown()
    {
        $instance = Phake::mock(__NAMESPACE__.'\NPMBridge');
        NPMBridge::handle($this->_eventUnknown, $instance);

        Phake::verify($instance, Phake::never())->postInstall(
            Phake::anyParameters()
        );
        Phake::verify($instance, Phake::never())->postUpdate(
            Phake::anyParameters()
        );
    }
}
