<?php

namespace Eloquent\Composer\NpmBridge;

use Composer\Composer;
use Composer\Package\Link;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Eloquent\Phony\Phpunit\Phony;
use PHPUnit\Framework\TestCase;
use Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException;

class NpmBridgeTest extends TestCase
{
    protected function setUp()
    {
        $this->io = Phony::mock('Composer\IO\IOInterface');
        $this->vendorFinder = Phony::mock('Eloquent\Composer\NpmBridge\NpmVendorFinder');
        $this->client = Phony::mock('Eloquent\Composer\NpmBridge\NpmClient');
        $this->client->valid->returns(true);
        $this->bridge = new NpmBridge($this->io->get(), $this->vendorFinder->get(), $this->client->get());

        $this->composer = new Composer();

        $this->rootPackage = new RootPackage('vendor/package', '1.0.0.0', '1.0.0');
        $this->packageA = new Package('vendorA/packageA', '1.0.0.0', '1.0.0');
        $this->packageB = new Package('vendorB/packageB', '1.0.0.0', '1.0.0');
        $this->packageC = new Package('vendorC/packageC', '1.0.0.0', '1.0.0');

        $this->packageC->setExtra(array('composer-npm-optional' => 1, 'composer-npm-timeout' => 900));

        $this->linkRoot1 = new Link('vendor/package', 'vendorX/packageX');
        $this->linkRoot2 = new Link('vendor/package', 'vendorY/packageY');
        $this->linkRoot3 = new Link('vendor/package', 'eloquent/composer-npm-bridge');

        $this->installationManager = Phony::mock('Composer\Installer\InstallationManager');
        $this->installationManager->getInstallPath->with($this->packageA)->returns('/path/to/install/a');
        $this->installationManager->getInstallPath->with($this->packageB)->returns('/path/to/install/b');
        $this->installationManager->getInstallPath->with($this->packageC)->returns('/path/to/install/c');

        $this->composer->setPackage($this->rootPackage);
        $this->composer->setInstallationManager($this->installationManager->get());
    }

    public function testInstall()
    {
        $this->rootPackage->setRequires([$this->linkRoot1, $this->linkRoot2, $this->linkRoot3]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([$this->packageA, $this->packageB, $this->packageC]);
        $this->bridge->install($this->composer);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Installing NPM dependencies for root project</info>'),
            $this->client->valid->calledWith(),
            $this->client->install->calledWith(null, true),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->client->valid->calledWith(),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            $this->client->install->calledWith('/path/to/install/a', false),
            $this->client->valid->calledWith(),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            $this->client->install->calledWith('/path/to/install/b', false),
            $this->client->valid->calledWith(),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorC/packageC</info>'),
            $this->client->setTimeout->calledWith(900),
            $this->client->install->calledWith('/path/to/install/c', false)
        );
    }

    public function testInstallProductionMode()
    {
        $this->rootPackage->setRequires([$this->linkRoot1, $this->linkRoot2, $this->linkRoot3]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([$this->packageA, $this->packageB, $this->packageC]);
        $this->bridge->install($this->composer, false);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Installing NPM dependencies for root project</info>'),
            $this->client->valid->calledWith(),
            $this->client->install->calledWith(null, false),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->client->valid->calledWith(),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            $this->client->install->calledWith('/path/to/install/a', false),
            $this->client->valid->calledWith(),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            $this->client->install->calledWith('/path/to/install/b', false),
            $this->client->valid->calledWith(),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorC/packageC</info>'),
            $this->client->setTimeout->calledWith(900),
            $this->client->install->calledWith('/path/to/install/c', false)
        );
    }

    public function testInstallRootDevDependenciesInDevMode()
    {
        $this->rootPackage->setDevRequires([$this->linkRoot3]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([]);
        $this->bridge->install($this->composer, true);

        $this->client->valid->calledWith();
        $this->client->install->calledWith(null, true);
    }

    public function testInstallRootDevDependenciesInProductionMode()
    {
        $this->rootPackage->setDevRequires([$this->linkRoot3]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([]);
        $this->bridge->install($this->composer, false);

        $this->client->install->never()->called();
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

    public function testInstallOptional()
    {
        $this->client->valid->returns(false);
        $this->client->install->with('/path/to/install/a')->throws(NpmNotFoundException::class);
        $this->client->install->with('/path/to/install/c')->returns();

        $this->rootPackage->setRequires([$this->linkRoot1, $this->linkRoot2]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([$this->packageC, $this->packageA]);
        try {
            $this->bridge->install($this->composer);
        } catch (Exception $err) {
        }

        Phony::inOrder(
            $this->io->write->calledWith('<info>Installing NPM dependencies for root project</info>'),
            $this->io->write->calledWith('Nothing to install'),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->client->valid->calledWith(),
            $this->io->write->calledWith('<info>Skipping optional NPM dependencies for vendorC/packageC as npm is unavailable</info>'),
            $this->client->valid->calledWith(),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            $this->client->install->calledWith('/path/to/install/a', false)
        );
    }

    public function testUpdate()
    {
        $this->rootPackage->setRequires([$this->linkRoot1, $this->linkRoot2, $this->linkRoot3]);
        $this->vendorFinder->find->with($this->composer, $this->bridge)->returns([$this->packageA, $this->packageB]);
        $this->bridge->update($this->composer);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Updating NPM dependencies for root project</info>'),
            $this->client->valid->calledWith(),
            $this->client->update->calledWith(),
            $this->client->install->calledWith(null, true),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->client->valid->calledWith(),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            $this->client->install->calledWith('/path/to/install/a', false),
            $this->client->valid->calledWith(),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            $this->client->install->calledWith('/path/to/install/b', false)
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
