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

use Composer\Util\ProcessExecutor;
use Icecave\Isolator\Isolator;
use PHPUnit_Framework_TestCase;
use Phake;
use Symfony\Component\Process\ExecutableFinder;

class NpmClientTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->processExecutor = Phake::mock('Composer\Util\ProcessExecutor');
        $this->executableFinder = Phake::mock('Symfony\Component\Process\ExecutableFinder');
        $this->isolator = Phake::mock(Isolator::className());
        $this->client = new NpmClient($this->processExecutor, $this->executableFinder, $this->isolator);

        Phake::when($this->executableFinder)->find('npm')->thenReturn('/path/to/npm');
        Phake::when($this->isolator)->getcwd()->thenReturn('/path/to/cwd');
        Phake::when($this->processExecutor)->execute(Phake::anyParameters())->thenReturn(0);
    }

    public function testConstructor()
    {
        $this->assertSame($this->processExecutor, $this->client->processExecutor());
        $this->assertSame($this->executableFinder, $this->client->executableFinder());
    }

    public function testConstructorDefaults()
    {
        $this->client = new NpmClient;

        $this->assertEquals(new ProcessExecutor, $this->client->processExecutor());
        $this->assertEquals(new ExecutableFinder, $this->client->executableFinder());
    }

    public function testInstall()
    {
        $this->assertNull($this->client->install('/path/to/project'));
        $this->assertNull($this->client->install('/path/to/project'));
        $preChdir = Phake::verify($this->isolator, Phake::times(2))->chdir('/path/to/project');
        $postChdir = Phake::verify($this->isolator, Phake::times(2))->chdir('/path/to/cwd');
        $install = Phake::verify($this->processExecutor, Phake::times(2))->execute("'/path/to/npm' 'install'");
        Phake::inOrder(
            Phake::verify($this->executableFinder)->find('npm'),
            $preChdir,
            $install,
            $postChdir,
            $preChdir,
            $install,
            $postChdir
        );
    }

    public function testInstallProductionMode()
    {
        $this->assertNull($this->client->install('/path/to/project', false));
        Phake::inOrder(
            Phake::verify($this->executableFinder)->find('npm'),
            Phake::verify($this->isolator)->chdir('/path/to/project'),
            Phake::verify($this->processExecutor)->execute("'/path/to/npm' 'install' '--production'"),
            Phake::verify($this->isolator)->chdir('/path/to/cwd')
        );
    }

    public function testInstallFailureNpmNotFound()
    {
        Phake::when($this->executableFinder)->find('npm')->thenReturn(null);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException');
        $this->client->install('/path/to/project');
    }

    public function testInstallFailureCommandFailed()
    {
        Phake::when($this->processExecutor)->execute(Phake::anyParameters())->thenReturn(1);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmCommandFailedException');
        $this->client->install('/path/to/project');
    }

    public function testUpdate()
    {
        $this->assertNull($this->client->update('/path/to/project'));
        $this->assertNull($this->client->update('/path/to/project'));
        $preChdir = Phake::verify($this->isolator, Phake::times(2))->chdir('/path/to/project');
        $postChdir = Phake::verify($this->isolator, Phake::times(2))->chdir('/path/to/cwd');
        $update = Phake::verify($this->processExecutor, Phake::times(2))->execute("'/path/to/npm' 'update'");
        Phake::inOrder(
            Phake::verify($this->executableFinder)->find('npm'),
            $preChdir,
            $update,
            $postChdir,
            $preChdir,
            $update,
            $postChdir
        );
    }

    public function testUpdateFailureNpmNotFound()
    {
        Phake::when($this->executableFinder)->find('npm')->thenReturn(null);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException');
        $this->client->update('/path/to/project');
    }

    public function testUpdateFailureCommandFailed()
    {
        Phake::when($this->processExecutor)->execute(Phake::anyParameters())->thenReturn(1);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmCommandFailedException');
        $this->client->update('/path/to/project');
    }

    public function testShrinkwrap()
    {
        $this->assertNull($this->client->shrinkwrap('/path/to/project'));
        $this->assertNull($this->client->shrinkwrap('/path/to/project'));
        $preChdir = Phake::verify($this->isolator, Phake::times(2))->chdir('/path/to/project');
        $postChdir = Phake::verify($this->isolator, Phake::times(2))->chdir('/path/to/cwd');
        $shrinkwrap = Phake::verify($this->processExecutor, Phake::times(2))->execute("'/path/to/npm' 'shrinkwrap'");
        Phake::inOrder(
            Phake::verify($this->executableFinder)->find('npm'),
            $preChdir,
            $shrinkwrap,
            $postChdir,
            $preChdir,
            $shrinkwrap,
            $postChdir
        );
    }

    public function testShrinkwrapFailureNpmNotFound()
    {
        Phake::when($this->executableFinder)->find('npm')->thenReturn(null);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException');
        $this->client->shrinkwrap('/path/to/project');
    }

    public function testShrinkwrapFailureCommandFailed()
    {
        Phake::when($this->processExecutor)->execute(Phake::anyParameters())->thenReturn(1);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmCommandFailedException');
        $this->client->shrinkwrap('/path/to/project');
    }
}
