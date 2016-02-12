<?php

/*
 * This file is part of the Composer NPM bridge package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Composer\NpmBridge;

use Composer\Util\ProcessExecutor;
use Eloquent\Phony\Phpunit\Phony;
use Icecave\Isolator\Isolator;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Process\ExecutableFinder;

class NpmClientTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->processExecutor = Phony::mock('Composer\Util\ProcessExecutor');
        $this->executableFinder = Phony::mock('Symfony\Component\Process\ExecutableFinder');
        $this->isolator = Phony::mock(Isolator::className());
        $this->client =
            new NpmClient($this->processExecutor->mock(), $this->executableFinder->mock(), $this->isolator->mock());

        $this->executableFinder->find('npm')->returns('/path/to/npm');
        $this->isolator->getcwd()->returns('/path/to/cwd');
        $this->processExecutor->execute('*')->returns(0);
    }

    public function testConstructor()
    {
        $this->assertSame($this->processExecutor->mock(), $this->client->processExecutor());
        $this->assertSame($this->executableFinder->mock(), $this->client->executableFinder());
    }

    public function testConstructorDefaults()
    {
        $this->client = new NpmClient();

        $this->assertEquals(new ProcessExecutor(), $this->client->processExecutor());
        $this->assertEquals(new ExecutableFinder(), $this->client->executableFinder());
    }

    public function testInstall()
    {
        $this->assertNull($this->client->install('/path/to/project'));
        $this->assertNull($this->client->install('/path/to/project'));
        Phony::inOrder(
            $this->executableFinder->find->calledWith('npm'),
            $this->isolator->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'install'"),
            $this->isolator->chdir->calledWith('/path/to/cwd'),
            $this->isolator->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'install'"),
            $this->isolator->chdir->calledWith('/path/to/cwd')
        );
    }

    public function testInstallProductionMode()
    {
        $this->assertNull($this->client->install('/path/to/project', false));
        Phony::inOrder(
            $this->executableFinder->find->calledWith('npm'),
            $this->isolator->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'install' '--production'"),
            $this->isolator->chdir->calledWith('/path/to/cwd')
        );
    }

    public function testInstallFailureNpmNotFound()
    {
        $this->executableFinder->find('npm')->returns(null);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException');
        $this->client->install('/path/to/project');
    }

    public function testInstallFailureCommandFailed()
    {
        $this->processExecutor->execute('*')->returns(1);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmCommandFailedException');
        $this->client->install('/path/to/project');
    }

    public function testUpdate()
    {
        $this->assertNull($this->client->update('/path/to/project'));
        $this->assertNull($this->client->update('/path/to/project'));
        Phony::inOrder(
            $this->executableFinder->find->calledWith('npm'),
            $this->isolator->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'update'"),
            $this->isolator->chdir->calledWith('/path/to/cwd'),
            $this->isolator->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'update'"),
            $this->isolator->chdir->calledWith('/path/to/cwd')
        );
    }

    public function testUpdateFailureNpmNotFound()
    {
        $this->executableFinder->find('npm')->returns(null);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException');
        $this->client->update('/path/to/project');
    }

    public function testUpdateFailureCommandFailed()
    {
        $this->processExecutor->execute('*')->returns(1);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmCommandFailedException');
        $this->client->update('/path/to/project');
    }

    public function testShrinkwrap()
    {
        $this->assertNull($this->client->shrinkwrap('/path/to/project'));
        $this->assertNull($this->client->shrinkwrap('/path/to/project'));
        Phony::inOrder(
            $this->executableFinder->find->calledWith('npm'),
            $this->isolator->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'shrinkwrap'"),
            $this->isolator->chdir->calledWith('/path/to/cwd'),
            $this->isolator->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'shrinkwrap'"),
            $this->isolator->chdir->calledWith('/path/to/cwd')
        );
    }

    public function testShrinkwrapFailureNpmNotFound()
    {
        $this->executableFinder->find('npm')->returns(null);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException');
        $this->client->shrinkwrap('/path/to/project');
    }

    public function testShrinkwrapFailureCommandFailed()
    {
        $this->processExecutor->execute('*')->returns(1);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmCommandFailedException');
        $this->client->shrinkwrap('/path/to/project');
    }
}
