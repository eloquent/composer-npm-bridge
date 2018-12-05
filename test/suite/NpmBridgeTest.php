<?php

namespace Eloquent\Composer\NpmBridge;

use Composer\Composer;
use Composer\Package\Link;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException;
use Eloquent\Phony\Phpunit\Phony;
use PHPUnit\Framework\TestCase;

class NpmBridgeTest extends TestCase
{
    protected function setUp()
    {
        $this->io = Phony::mock('Composer\IO\IOInterface');
        $this->vendorFinder = Phony::mock('Eloquent\Composer\NpmBridge\NpmVendorFinder');
        $this->client = Phony::mock('Eloquent\Composer\NpmBridge\NpmClient');
        $this->bridge = new NpmBridge($this->io->get(), $this->vendorFinder->get(), $this->client->get());

        $this->client->isAvailable->returns(true);

        $this->composer = new Composer();

        $this->rootPackage = new RootPackage('vendor/package', '1.0.0.0', '1.0.0');
        $this->packageA = new Package('vendorA/packageA', '1.0.0.0', '1.0.0');
        $this->packageB = new Package('vendorB/packageB', '1.0.0.0', '1.0.0');

        $this->linkRoot1 = new Link('vendor/package', 'vendorX/packageX');
        $this->linkRoot2 = new Link('vendor/package', 'vendorY/packageY');
        $this->linkRoot3 = new Link('vendor/package', 'eloquent/composer-npm-bridge');

        $this->installationManager = Phony::mock('Composer\Installer\InstallationManager');
        $this->installationManager->getInstallPath->with($this->packageA)->returns('/path/to/install/a');
        $this->installationManager->getInstallPath->with($this->packageB)->returns('/path/to/install/b');

        $this->composer->setPackage($this->rootPackage);
        $this->composer->setInstallationManager($this->installationManager->get());
    }

    public function testInstall()
    {
        $this->rootPackage->setRequires([$this->linkRoot1, $this->linkRoot2, $this->linkRoot3]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([$this->packageA, $this->packageB]);
        $this->bridge->install($this->composer);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Installing NPM dependencies for root project</info>'),
            $this->client->install->calledWith(null, true, null),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            $this->client->install->calledWith('/path/to/install/a', false, null),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            $this->client->install->calledWith('/path/to/install/b', false, null)
        );
    }

    public function testInstallProductionMode()
    {
        $this->rootPackage->setRequires([$this->linkRoot1, $this->linkRoot2, $this->linkRoot3]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([$this->packageA, $this->packageB]);
        $this->bridge->install($this->composer, false);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Installing NPM dependencies for root project</info>'),
            $this->client->install->calledWith(null, false, null),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            $this->client->install->calledWith('/path/to/install/a', false, null),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            $this->client->install->calledWith('/path/to/install/b', false, null)
        );
    }

    public function testInstallRootDevDependenciesInDevMode()
    {
        $this->rootPackage->setDevRequires([$this->linkRoot3]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([]);
        $this->bridge->install($this->composer, true);

        $this->client->install->calledWith(null, true, null);
    }

    public function testInstallRootDevDependenciesInProductionMode()
    {
        $this->rootPackage->setDevRequires([$this->linkRoot3]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([]);
        $this->bridge->install($this->composer, false);

        $this->client->install->never()->called();
    }

    public function testInstallWithRootTimeout()
    {
        $this->rootPackage->setRequires([$this->linkRoot3]);
        $this->rootPackage->setExtra([
            NpmBridge::EXTRA_KEY => [
                NpmBridge::EXTRA_KEY_TIMEOUT => 111,
            ],
        ]);
        $this->bridge->install($this->composer, false);

        $this->client->install->calledWith(null, false, 111);
    }

    public function testInstallWithDependencyTimeout()
    {
        $this->rootPackage->setRequires([$this->linkRoot3]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([$this->packageA, $this->packageB]);
        $this->packageA->setExtra([
            NpmBridge::EXTRA_KEY => [
                NpmBridge::EXTRA_KEY_TIMEOUT => 111,
            ],
        ]);
        $this->bridge->install($this->composer);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Installing NPM dependencies for root project</info>'),
            $this->client->install->calledWith(null, true, null),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            $this->client->install->calledWith('/path/to/install/a', false, 111),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            $this->client->install->calledWith('/path/to/install/b', false, null)
        );
    }

    public function testInstallRootFailureWhenUnavailable()
    {
        $this->client->isAvailable->returns(false);
        $this->expected = new NpmNotFoundException();
        $this->client->install->throws($this->expected);
        $this->rootPackage->setRequires([$this->linkRoot3]);

        $this->expectExceptionObject($this->expected);
        $this->bridge->install($this->composer, false);
    }

    public function testInstallRootOptionalWhenUnavailable()
    {
        $this->client->isAvailable->returns(false);
        $this->rootPackage->setRequires([$this->linkRoot3]);
        $this->rootPackage->setExtra([
            NpmBridge::EXTRA_KEY => [
                NpmBridge::EXTRA_KEY_OPTIONAL => true,
            ],
        ]);
        $this->bridge->install($this->composer, false);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Installing NPM dependencies for root project</info>'),
            $this->io->write->calledWith('Skipping as NPM is unavailable')
        );
        $this->client->install->never()->called();
    }

    public function testInstallRootOptionalWhenAvailable()
    {
        $this->client->isAvailable->returns(true);
        $this->rootPackage->setRequires([$this->linkRoot3]);
        $this->rootPackage->setExtra([
            NpmBridge::EXTRA_KEY => [
                NpmBridge::EXTRA_KEY_OPTIONAL => true,
            ],
        ]);
        $this->bridge->install($this->composer, false);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Installing NPM dependencies for root project</info>'),
            $this->client->install->calledWith(null, false, null)
        );
    }

    public function testInstallDependencyFailureWhenUnavailable()
    {
        $this->client->isAvailable->returns(false);
        $this->expected = new NpmNotFoundException();
        $this->client->install->throws($this->expected);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([$this->packageA, $this->packageB]);

        $this->expectExceptionObject($this->expected);
        $this->bridge->install($this->composer, false);
    }

    public function testInstallDependencyOptionalWhenUnavailable()
    {
        $this->client->isAvailable->returns(false);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([$this->packageA, $this->packageB]);
        $this->packageA->setExtra([
            NpmBridge::EXTRA_KEY => [
                NpmBridge::EXTRA_KEY_OPTIONAL => true,
            ],
        ]);
        $this->packageB->setExtra([
            NpmBridge::EXTRA_KEY => [
                NpmBridge::EXTRA_KEY_OPTIONAL => true,
            ],
        ]);
        $this->bridge->install($this->composer, false);

        Phony::inOrder(
            $this->io->write
                ->calledWith('Skipping optional NPM dependencies for vendorA/packageA as NPM is unavailable'),
            $this->io->write
                ->calledWith('Skipping optional NPM dependencies for vendorB/packageB as NPM is unavailable')
        );
        $this->client->install->never()->called();
    }

    public function testInstallDependencyOptionalWhenAvailable()
    {
        $this->client->isAvailable->returns(true);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([$this->packageA, $this->packageB]);
        $this->packageA->setExtra([
            NpmBridge::EXTRA_KEY => [
                NpmBridge::EXTRA_KEY_OPTIONAL => true,
            ],
        ]);
        $this->packageB->setExtra([
            NpmBridge::EXTRA_KEY => [
                NpmBridge::EXTRA_KEY_OPTIONAL => true,
            ],
        ]);
        $this->bridge->install($this->composer, false);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            $this->client->install->calledWith('/path/to/install/a', false, null),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            $this->client->install->calledWith('/path/to/install/b', false, null)
        );
    }

    public function testInstallNothing()
    {
        $this->rootPackage->setRequires([$this->linkRoot1, $this->linkRoot2]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([]);
        $this->bridge->install($this->composer);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Installing NPM dependencies for root project</info>'),
            $this->io->write->calledWith('Nothing to install'),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->io->write->calledWith('Nothing to install')
        );
    }

    public function testUpdate()
    {
        $this->rootPackage->setRequires([$this->linkRoot1, $this->linkRoot2, $this->linkRoot3]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([$this->packageA, $this->packageB]);
        $this->bridge->update($this->composer);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Updating NPM dependencies for root project</info>'),
            $this->client->update->calledWith(null, null),
            $this->client->install->calledWith(null, true, null),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            $this->client->install->calledWith('/path/to/install/a', false, null),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            $this->client->install->calledWith('/path/to/install/b', false, null)
        );
    }

    public function testUpdateNothing()
    {
        $this->rootPackage->setRequires([$this->linkRoot1, $this->linkRoot2]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([]);
        $this->bridge->update($this->composer);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Updating NPM dependencies for root project</info>'),
            $this->io->write->calledWith('Nothing to update'),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->io->write->calledWith('Nothing to install')
        );
    }

    public function testIsDependantPackage()
    {
        $this->packageA->setRequires([$this->linkRoot3]);
        $this->packageB->setDevRequires([$this->linkRoot3]);

        $this->assertTrue($this->bridge->isDependantPackage($this->packageA));
        $this->assertFalse($this->bridge->isDependantPackage($this->packageB));
        $this->assertTrue($this->bridge->isDependantPackage($this->packageA, false));
        $this->assertFalse($this->bridge->isDependantPackage($this->packageB, false));
        $this->assertTrue($this->bridge->isDependantPackage($this->packageA, true));
        $this->assertTrue($this->bridge->isDependantPackage($this->packageB, true));
    }
}
