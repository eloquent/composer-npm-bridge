<?php

namespace Eloquent\Composer\NpmBridge;

use Eloquent\Phony\Phpunit\Phony;
use PHPUnit\Framework\TestCase;

class NpmClientTest extends TestCase
{
    protected function setUp()
    {
        $this->processExecutor = Phony::mock('Composer\Util\ProcessExecutor');
        $this->executableFinder = Phony::mock('Symfony\Component\Process\ExecutableFinder');
        $this->getcwd = Phony::stub();
        $this->chdir = Phony::stub();
        $this->client = new NpmClient(
            $this->processExecutor->get(),
            $this->executableFinder->get(),
            $this->getcwd,
            $this->chdir,
            $this->processExecutor->className()
        );

        $this->processExecutor->execute->returns(0);
        $this->executableFinder->find->with('npm')->returns('/path/to/npm');
        $this->getcwd->returns('/path/to/cwd');

        $this->processExecutorClass = Phony::onStatic($this->processExecutor);
    }

    public function testInstall()
    {
        $this->client->install();

        Phony::inOrder(
            $this->executableFinder->find->calledWith('npm'),
            $this->processExecutorClass->getTimeout->called(),
            $this->processExecutorClass->setTimeout->calledWith(NpmClient::DEFAULT_TIMEOUT),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'install'"),
            $this->processExecutorClass->setTimeout->calledWith(
                $this->processExecutorClass->getTimeout->firstCall()->returnValue()
            )
        );
        $this->chdir->never()->called();
    }

    public function testInstallWithWorkingDirectory()
    {
        $this->client->install('/path/to/project');

        Phony::inOrder(
            $this->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'install'"),
            $this->chdir->calledWith('/path/to/cwd')
        );
    }

    public function testInstallWithoutDevMode()
    {
        $this->client->install(null, false);

        $this->processExecutor->execute->calledWith("'/path/to/npm' 'install' '--production'");
    }

    public function testInstallFailureNpmNotFound()
    {
        $this->executableFinder->find->with('npm')->returns(null);

        $this->expectException('Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException');
        $this->client->install('/path/to/project');
    }

    public function testInstallFailureCommandFailed()
    {
        $this->processExecutor->execute->returns(1);

        $this->expectException('Eloquent\Composer\NpmBridge\Exception\NpmCommandFailedException');
        $this->client->install('/path/to/project');
    }

    public function testUpdate()
    {
        $this->client->update();

        Phony::inOrder(
            $this->executableFinder->find->calledWith('npm'),
            $this->processExecutorClass->getTimeout->called(),
            $this->processExecutorClass->setTimeout->calledWith(NpmClient::DEFAULT_TIMEOUT),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'update'"),
            $this->processExecutorClass->setTimeout->calledWith(
                $this->processExecutorClass->getTimeout->firstCall()->returnValue()
            )
        );
        $this->chdir->never()->called();
    }

    public function testUpdateWithWorkingDirectory()
    {
        $this->client->update('/path/to/project');

        Phony::inOrder(
            $this->chdir->calledWith('/path/to/project'),
            $this->processExecutor->execute->calledWith("'/path/to/npm' 'update'"),
            $this->chdir->calledWith('/path/to/cwd')
        );
    }

    public function testUpdateFailureNpmNotFound()
    {
        $this->executableFinder->find->with('npm')->returns(null);

        $this->expectException('Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException');
        $this->client->update('/path/to/project');
    }

    public function testUpdateFailureCommandFailed()
    {
        $this->processExecutor->execute->returns(1);

        $this->expectException('Eloquent\Composer\NpmBridge\Exception\NpmCommandFailedException');
        $this->client->update('/path/to/project');
    }

    public function testIsAvailable()
    {
        $this->executableFinder->find->with('npm')->returns(null);
        $this->assertSame(
            false,
            $this->client->isAvailable()
        );

        $this->executableFinder->find->with('npm')->returns('/path/to/npm');
        $this->assertSame(
            true,
            $this->client->isAvailable()
        );
    }
}
