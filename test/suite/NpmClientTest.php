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
        $this->client = new NpmClient($this->processExecutor, $this->executableFinder);

        Phake::when($this->executableFinder)->find('npm')->thenReturn('/path/to/npm');
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
        Phake::verify($this->executableFinder)->find('npm');
        Phake::verify($this->processExecutor, Phake::times(2))->execute("'/path/to/npm' 'install'", null, '/path/to/project');
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
        Phake::verify($this->executableFinder)->find('npm');
        Phake::verify($this->processExecutor, Phake::times(2))->execute("'/path/to/npm' 'update'", null, '/path/to/project');
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
        Phake::verify($this->executableFinder)->find('npm');
        Phake::verify($this->processExecutor, Phake::times(2))->execute("'/path/to/npm' 'shrinkwrap'", null, '/path/to/project');
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
