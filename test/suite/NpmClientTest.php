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
use PHPUnit_Framework_TestCase;
use Symfony\Component\Process\ExecutableFinder;

class NpmClientTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->processExecutor = Phony::mock('Composer\Util\ProcessExecutor');
        $this->executableFinder = Phony::mock('Symfony\Component\Process\ExecutableFinder');
        $this->getcwd = Phony::stub();
        $this->chdir = Phony::stub();
        $this->client =
            new NpmClient($this->processExecutor->mock(), $this->executableFinder->mock(), $this->getcwd, $this->chdir);

        $this->processExecutor->execute('*')->returns(0);
        $this->executableFinder->find('npm')->returns('/path/to/npm');
        $this->getcwd->returns('/path/to/cwd');
    }

    public function testInstall()
    {
        $this->assertNull($this->client->install('/path/to/project'));
        $this->assertNull($this->client->install('/path/to/project'));
        Phony::inOrder(
            $this->executableFinder->find->calledWith('npm'),
            $this->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'install'"),
            $this->chdir->calledWith('/path/to/cwd'),
            $this->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'install'"),
            $this->chdir->calledWith('/path/to/cwd')
        );
    }

    public function testInstallProductionMode()
    {
        $this->assertNull($this->client->install('/path/to/project', false));
        Phony::inOrder(
            $this->executableFinder->find->calledWith('npm'),
            $this->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'install' '--production'"),
            $this->chdir->calledWith('/path/to/cwd')
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
            $this->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'update'"),
            $this->chdir->calledWith('/path/to/cwd'),
            $this->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'update'"),
            $this->chdir->calledWith('/path/to/cwd')
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
            $this->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'shrinkwrap'"),
            $this->chdir->calledWith('/path/to/cwd'),
            $this->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'shrinkwrap'"),
            $this->chdir->calledWith('/path/to/cwd')
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
