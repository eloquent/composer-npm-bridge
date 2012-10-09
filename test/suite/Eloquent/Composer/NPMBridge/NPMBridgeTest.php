<?php

/*
 * This file is part of the Composer NPM bridge package.
 *
 * Copyright © 2012 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Composer\NPMBridge;

use Composer\Script\ScriptEvents;
use Eloquent\Liberator\Liberator;
use Phake;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class NPMBridgeTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->_executableFinder = Phake::mock('Symfony\Component\Process\ExecutableFinder');
        $this->_isolator = Phake::mock('Icecave\Isolator\Isolator');
        $this->_bridge = Phake::partialMock(
            __NAMESPACE__.'\NPMBridge',
            $this->_executableFinder,
            $this->_isolator
        );

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

    public function testConstructor()
    {
        $this->assertSame($this->_executableFinder, $this->_bridge->executableFinder());
    }

    public function testConstructorDefaults()
    {
        $bridge = new NPMBridge;

        $this->assertInstanceOf(
            'Symfony\Component\Process\ExecutableFinder',
            $this->_bridge->executableFinder()
        );
    }

    public function testPostInstall()
    {
        $io = Phake::mock('Composer\IO\IOInterface');
        Phake::when($this->_eventPostInstall)->getIO()->thenReturn($io);
        Phake::when($this->_bridge)
            ->executeNpm(Phake::anyParameters())
            ->thenReturn(null)
        ;
        $this->_bridge->postInstall($this->_eventPostInstall);

        Phake::inOrder(
            Phake::verify($io)->write(
                'Installing NPM dependencies...',
                true
            ),
            Phake::verify($this->_bridge)->executeNpm(
                array('install'),
                $this->identicalTo($io)
            ),
            Phake::verify($this->_bridge)->executeNpm(
                array('shrinkwrap'),
                $this->identicalTo($io)
            )
        );
    }

    public function testPostUpdate()
    {
        $io = Phake::mock('Composer\IO\IOInterface');
        Phake::when($this->_eventPostUpdate)->getIO()->thenReturn($io);
        Phake::when($this->_bridge)
            ->unwrap(Phake::anyParameters())
            ->thenReturn(null)
        ;
        Phake::when($this->_bridge)
            ->executeNpm(Phake::anyParameters())
            ->thenReturn(null)
        ;
        $this->_bridge->postUpdate($this->_eventPostUpdate);

        Phake::inOrder(
            Phake::verify($io)->write(
                'Updating NPM dependencies...',
                true
            ),
            Phake::verify($this->_bridge)->unwrap($this->identicalTo($io)),
            Phake::verify($this->_bridge)->executeNpm(
                array('update'),
                $this->identicalTo($io)
            ),
            Phake::verify($this->_bridge)->executeNpm(
                array('shrinkwrap'),
                $this->identicalTo($io)
            )
        );
    }

    public function testExecuteNpm()
    {
        $process = Phake::mock('Symfony\Component\Process\Process');
        Phake::when($this->_bridge)
            ->createNpmProcess(Phake::anyParameters())
            ->thenReturn($process)
        ;
        Phake::when($process)
            ->run(Phake::anyParameters())
            ->thenGetReturnByLambda(function($callback) {
                $callback(Process::OUT, 'qux');
            }
        );
        $arguments = array('bar', 'baz');
        $io = Phake::mock('Composer\IO\IOInterface');
        Liberator::liberate($this->_bridge)->executeNpm($arguments, $io);

        Phake::inOrder(
            Phake::verify($process)->run($this->isInstanceOf('Closure')),
            Phake::verify($io)->write('qux', false)
        );
    }

    public function testExecuteNpmErrorOutput()
    {
        $process = Phake::mock('Symfony\Component\Process\Process');
        Phake::when($this->_bridge)
            ->createNpmProcess(Phake::anyParameters())
            ->thenReturn($process)
        ;
        Phake::when($process)
            ->run(Phake::anyParameters())
            ->thenGetReturnByLambda(function($callback) {
                $callback(Process::ERR, 'qux');
            }
        );
        $arguments = array('bar', 'baz');
        $io = Phake::mock('Composer\IO\IOInterface');
        Liberator::liberate($this->_bridge)->executeNpm($arguments, $io);

        Phake::inOrder(
            Phake::verify($process)->run($this->isInstanceOf('Closure')),
            Phake::verify($this->_isolator)->fwrite(STDERR, 'qux')
        );
    }

    public function testUnwrap()
    {
        $io = Phake::mock('Composer\IO\IOInterface');
        Phake::when($this->_isolator)
            ->is_file(Phake::anyParameters())
            ->thenReturn(true)
        ;
        Liberator::liberate($this->_bridge)->unwrap($io);

        Phake::inOrder(
            Phake::verify($io)->write('Removing NPM shrinkwrap... ', false),
            Phake::verify($this->_isolator)->is_file('./npm-shrinkwrap.json'),
            Phake::verify($this->_isolator)->unlink('./npm-shrinkwrap.json'),
            Phake::verify($io)->write('done.', true)
        );
    }

    public function testUnwrapNoFilePresent()
    {
        $io = Phake::mock('Composer\IO\IOInterface');
        Phake::when($this->_isolator)
            ->is_file(Phake::anyParameters())
            ->thenReturn(false)
        ;
        Liberator::liberate($this->_bridge)->unwrap($io);

        Phake::inOrder(
            Phake::verify($io)->write('Removing NPM shrinkwrap... ', false),
            Phake::verify($this->_isolator)->is_file('./npm-shrinkwrap.json'),
            Phake::verify($io)->write('nothing to do.', true)
        );
        Phake::verify($this->_isolator, Phake::never())->unlink('./npm-shrinkwrap.json');
    }

    public function testCreateNpmProcess()
    {
        $process = Phake::mock('Symfony\Component\Process\Process');
        $processBuilder = Phake::mock('Symfony\Component\Process\ProcessBuilder');
        Phake::when($processBuilder)
            ->getProcess(Phake::anyParameters())
            ->thenReturn($process)
        ;
        Phake::when($this->_bridge)
            ->createProcessBuilder(Phake::anyParameters())
            ->thenReturn($processBuilder)
        ;
        Phake::when($this->_bridge)
            ->npmPath(Phake::anyParameters())
            ->thenReturn('bar')
        ;

        $this->assertSame(
            $process,
            Liberator::liberate($this->_bridge)->createNpmProcess(array('baz', 'qux'))
        );
        Phake::inOrder(
            Phake::verify($this->_bridge)->npmPath(),
            Phake::verify($this->_bridge)->createProcessBuilder(array('bar', 'baz', 'qux'))
        );
    }

    public function testNpmPath()
    {
        Phake::when($this->_executableFinder)
            ->find(Phake::anyParameters())
            ->thenReturn('bar')
        ;
        $bridge = Liberator::liberate($this->_bridge);

        $this->assertSame('bar', $bridge->npmPath());
        $this->assertSame('bar', $bridge->npmPath());
        Phake::verify($this->_executableFinder)->find('npm');
    }

    public function testNpmPathFailure()
    {
        Phake::when($this->_executableFinder)
            ->find(Phake::anyParameters())
            ->thenReturn(null)
        ;
        $bridge = Liberator::liberate($this->_bridge);

        $this->setExpectedException('RuntimeException', 'Unable to locate npm executable.');
        $bridge->npmPath();
    }

    public function testCreateProcessBuilder()
    {
        $arguments = array('bar', 'baz');
        $expected = new ProcessBuilder($arguments);

        $this->assertEquals(
            $expected,
            Liberator::liberate($this->_bridge)->createProcessBuilder($arguments)
        );
    }
}
