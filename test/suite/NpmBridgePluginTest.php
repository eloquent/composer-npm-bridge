<?php

namespace Eloquent\Composer\NpmBridge;

use Composer\Composer;
use Composer\IO\NullIO;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Eloquent\Phony\Phpunit\Phony;
use PHPUnit\Framework\TestCase;

class NpmBridgePluginTest extends TestCase
{
    protected function setUp()
    {
        $this->bridgeFactory = Phony::mock('Eloquent\Composer\NpmBridge\NpmBridgeFactory');
        $this->plugin = new NpmBridgePlugin($this->bridgeFactory->get());

        $this->bridge = Phony::mock('Eloquent\Composer\NpmBridge\NpmBridge');
        $this->composer = new Composer();
        $this->io = new NullIO();

        $this->bridgeFactory->createBridge->returns($this->bridge);
    }

    protected function tearDown()
    {
        Phony::restoreGlobalFunctions();
    }


    public function testConstructorWithoutArguments()
    {
        $this->assertInstanceOf('Eloquent\Composer\NpmBridge\NpmBridgePlugin', new NpmBridgePlugin());
    }

    public function testActivate()
    {
        $classExists = Phony::spyGlobal('class_exists', __NAMESPACE__);
        $this->plugin->activate($this->composer, $this->io);

        $classExists->calledWith(NpmBridge::class);
        $classExists->calledWith(NpmBridgeFactory::class);
        $classExists->calledWith(NpmClient::class);
        $classExists->calledWith(NpmBridge::class);
        $classExists->calledWith(NpmVendorFinder::class);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                ScriptEvents::POST_INSTALL_CMD => 'onPostInstallCmd',
                ScriptEvents::POST_UPDATE_CMD => 'onPostUpdateCmd',
            ],
            $this->plugin->getSubscribedEvents()
        );
    }

    public function testOnPostInstallCmd()
    {
        $this->plugin->onPostInstallCmd(new Event(ScriptEvents::POST_INSTALL_CMD, $this->composer, $this->io, true));

        Phony::inOrder(
            $this->bridgeFactory->createBridge->calledWith($this->io),
            $this->bridge->install->calledWith($this->composer, true)
        );
    }

    public function testOnPostInstallCmdProductionMode()
    {
        $this->plugin->onPostInstallCmd(new Event(ScriptEvents::POST_INSTALL_CMD, $this->composer, $this->io, false));

        Phony::inOrder(
            $this->bridgeFactory->createBridge->calledWith($this->io),
            $this->bridge->install->calledWith($this->composer, false)
        );
    }

    public function testOnPostUpdateCmd()
    {
        $this->plugin->onPostUpdateCmd(new Event(ScriptEvents::POST_UPDATE_CMD, $this->composer, $this->io));

        Phony::inOrder(
            $this->bridgeFactory->createBridge->calledWith($this->io),
            $this->bridge->update->calledWith($this->composer)
        );
    }
}
