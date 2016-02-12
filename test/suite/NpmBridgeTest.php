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

use Composer\Composer;
use Composer\Package\Link;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Eloquent\Phony\Phpunit\Phony;
use PHPUnit_Framework_TestCase;

class NpmBridgeTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->io = Phony::mock('Composer\IO\IOInterface');
        $this->vendorFinder = Phony::mock('Eloquent\Composer\NpmBridge\NpmVendorFinder');
        $this->client = Phony::mock('Eloquent\Composer\NpmBridge\NpmClient');
        $this->bridge = new NpmBridge($this->io->mock(), $this->vendorFinder->mock(), $this->client->mock());

        $this->composer = new Composer();

        $this->rootPackage = new RootPackage('vendor/package', '1.0.0.0', '1.0.0');
        $this->packageA = new Package('vendorA/packageA', '1.0.0.0', '1.0.0');
        $this->packageB = new Package('vendorB/packageB', '1.0.0.0', '1.0.0');

        $this->linkRoot1 = new Link('vendor/package', 'vendorX/packageX');
        $this->linkRoot2 = new Link('vendor/package', 'vendorY/packageY');
        $this->linkRoot3 = new Link('vendor/package', 'eloquent/composer-npm-bridge');

        $this->installationManager = Phony::mock('Composer\Installer\InstallationManager');
        $this->installationManager->getInstallPath($this->packageA)->returns('/path/to/install/a');
        $this->installationManager->getInstallPath($this->packageB)->returns('/path/to/install/b');

        $this->composer->setPackage($this->rootPackage);
        $this->composer->setInstallationManager($this->installationManager->mock());
    }

    public function testInstall()
    {
        $this->rootPackage->setRequires(array($this->linkRoot1, $this->linkRoot2, $this->linkRoot3));
        $this->vendorFinder->find($this->composer, $this->bridge)->returns(array($this->packageA, $this->packageB));
        $this->bridge->install($this->composer);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Installing NPM dependencies for root project</info>'),
            $this->client->install->calledWith(null, true),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            $this->client->install->calledWith('/path/to/install/a', false),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            $this->client->install->calledWith('/path/to/install/b', false)
        );
    }

    public function testInstallProductionMode()
    {
        $this->rootPackage->setRequires(array($this->linkRoot1, $this->linkRoot2, $this->linkRoot3));
        $this->vendorFinder->find($this->composer, $this->bridge)->returns(array($this->packageA, $this->packageB));
        $this->bridge->install($this->composer, false);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Installing NPM dependencies for root project</info>'),
            $this->client->install->calledWith(null, false),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            $this->client->install->calledWith('/path/to/install/a', false),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            $this->client->install->calledWith('/path/to/install/b', false)
        );
    }

    public function testInstallRootDevDependenciesInDevMode()
    {
        $this->rootPackage->setDevRequires(array($this->linkRoot3));
        $this->vendorFinder->find($this->composer, $this->bridge)->returns(array());
        $this->bridge->install($this->composer, true);

        $this->client->install->calledWith(null, true);
    }

    public function testInstallRootDevDependenciesInProductionMode()
    {
        $this->rootPackage->setDevRequires(array($this->linkRoot3));
        $this->vendorFinder->find($this->composer, $this->bridge)->returns(array());
        $this->bridge->install($this->composer, false);

        $this->client->install->never()->called();
    }

    public function testInstallNothing()
    {
        $this->rootPackage->setRequires(array($this->linkRoot1, $this->linkRoot2));
        $this->vendorFinder->find($this->composer, $this->bridge)->returns(array());
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
        $this->rootPackage->setRequires(array($this->linkRoot1, $this->linkRoot2, $this->linkRoot3));
        $this->vendorFinder->find($this->composer, $this->bridge)->returns(array($this->packageA, $this->packageB));
        $this->bridge->update($this->composer);

        Phony::inOrder(
            $this->io->write->calledWith('<info>Updating NPM dependencies for root project</info>'),
            $this->client->update->calledWith(),
            $this->client->install->calledWith(null, true),
            $this->client->shrinkwrap->calledWith(),
            $this->io->write->calledWith('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            $this->client->install->calledWith('/path/to/install/a', false),
            $this->io->write->calledWith('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            $this->client->install->calledWith('/path/to/install/b', false)
        );
    }

    public function testUpdateNothing()
    {
        $this->rootPackage->setRequires(array($this->linkRoot1, $this->linkRoot2));
        $this->vendorFinder->find($this->composer, $this->bridge)->returns(array());
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
        $this->packageA->setRequires(array($this->linkRoot3));
        $this->packageB->setDevRequires(array($this->linkRoot3));

        $this->assertTrue($this->bridge->isDependantPackage($this->packageA));
        $this->assertFalse($this->bridge->isDependantPackage($this->packageB));
        $this->assertTrue($this->bridge->isDependantPackage($this->packageA, false));
        $this->assertFalse($this->bridge->isDependantPackage($this->packageB, false));
        $this->assertTrue($this->bridge->isDependantPackage($this->packageA, true));
        $this->assertTrue($this->bridge->isDependantPackage($this->packageB, true));
    }
}
