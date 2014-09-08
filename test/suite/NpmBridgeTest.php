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

use Composer\Composer;
use Composer\IO\NullIO;
use Composer\Package\Link;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Util\ProcessExecutor;
use PHPUnit_Framework_TestCase;
use Phake;

class NpmBridgeTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->io = Phake::mock('Composer\IO\IOInterface');
        $this->vendorFinder = Phake::mock('Eloquent\Composer\NpmBridge\NpmVendorFinderInterface');
        $this->client = Phake::mock('Eloquent\Composer\NpmBridge\NpmClientInterface');
        $this->bridge = new NpmBridge($this->io, $this->vendorFinder, $this->client);

        $this->composer = new Composer;

        $this->rootPackage = new RootPackage('vendor/package', '1.0.0.0', '1.0.0');
        $this->packageA = new Package('vendorA/packageA', '1.0.0.0', '1.0.0');
        $this->packageB = new Package('vendorB/packageB', '1.0.0.0', '1.0.0');

        $this->linkRoot1 = new Link('vendor/package', 'vendorX/packageX');
        $this->linkRoot2 = new Link('vendor/package', 'vendorY/packageY');
        $this->linkRoot3 = new Link('vendor/package', 'eloquent/composer-npm-bridge');

        $this->installationManager = Phake::mock('Composer\Installer\InstallationManager');
        Phake::when($this->installationManager)->getInstallPath($this->packageA)->thenReturn('/path/to/install/a');
        Phake::when($this->installationManager)->getInstallPath($this->packageB)->thenReturn('/path/to/install/b');

        $this->composer->setPackage($this->rootPackage);
        $this->composer->setInstallationManager($this->installationManager);
    }

    public function testConstructor()
    {
        $this->assertSame($this->io, $this->bridge->io());
        $this->assertSame($this->vendorFinder, $this->bridge->vendorFinder());
        $this->assertSame($this->client, $this->bridge->client());
    }

    public function testConstructorDefaults()
    {
        $this->bridge = new NpmBridge;

        $this->assertEquals(new NullIO, $this->bridge->io());
        $this->assertEquals(new NpmVendorFinder, $this->bridge->vendorFinder());
        $this->assertEquals(new NpmClient(new ProcessExecutor(new NullIO)), $this->bridge->client());
    }

    public function testInstall()
    {
        $this->rootPackage->setRequires(array($this->linkRoot1, $this->linkRoot2, $this->linkRoot3));
        Phake::when($this->vendorFinder)->find($this->composer, $this->bridge)
            ->thenReturn(array($this->packageA, $this->packageB));
        $this->bridge->install($this->composer);

        Phake::inOrder(
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for root project</info>'),
            Phake::verify($this->client)->install(null, true),
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for Composer dependencies</info>'),
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            Phake::verify($this->client)->install('/path/to/install/a', false),
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            Phake::verify($this->client)->install('/path/to/install/b', false)
        );
    }

    public function testInstallProductionMode()
    {
        $this->rootPackage->setRequires(array($this->linkRoot1, $this->linkRoot2, $this->linkRoot3));
        Phake::when($this->vendorFinder)->find($this->composer, $this->bridge)
            ->thenReturn(array($this->packageA, $this->packageB));
        $this->bridge->install($this->composer, false);

        Phake::inOrder(
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for root project</info>'),
            Phake::verify($this->client)->install(null, false),
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for Composer dependencies</info>'),
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            Phake::verify($this->client)->install('/path/to/install/a', false),
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            Phake::verify($this->client)->install('/path/to/install/b', false)
        );
    }

    public function testInstallRootDevDependenciesInDevMode()
    {
        $this->rootPackage->setDevRequires(array($this->linkRoot3));
        Phake::when($this->vendorFinder)->find($this->composer, $this->bridge)->thenReturn(array());
        $this->bridge->install($this->composer, true);

        Phake::verify($this->client)->install(null, true);
    }

    public function testInstallRootDevDependenciesInProductionMode()
    {
        $this->rootPackage->setDevRequires(array($this->linkRoot3));
        Phake::when($this->vendorFinder)->find($this->composer, $this->bridge)->thenReturn(array());
        $this->bridge->install($this->composer, false);

        Phake::verify($this->client, Phake::never())->install(Phake::anyParameters());
    }

    public function testInstallNothing()
    {
        $this->rootPackage->setRequires(array($this->linkRoot1, $this->linkRoot2));
        Phake::when($this->vendorFinder)->find($this->composer, $this->bridge)->thenReturn(array());
        $this->bridge->install($this->composer);

        $nothing = Phake::verify($this->io, Phake::times(2))->write('Nothing to install');
        Phake::inOrder(
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for root project</info>'),
            $nothing,
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for Composer dependencies</info>'),
            $nothing
        );
    }

    public function testUpdate()
    {
        $this->rootPackage->setRequires(array($this->linkRoot1, $this->linkRoot2, $this->linkRoot3));
        Phake::when($this->vendorFinder)->find($this->composer, $this->bridge)
            ->thenReturn(array($this->packageA, $this->packageB));
        $this->bridge->update($this->composer);

        Phake::inOrder(
            Phake::verify($this->io)->write('<info>Updating NPM dependencies for root project</info>'),
            Phake::verify($this->client)->update(),
            Phake::verify($this->client)->install(null, true),
            Phake::verify($this->client)->shrinkwrap(),
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for Composer dependencies</info>'),
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for vendorA/packageA</info>'),
            Phake::verify($this->client)->install('/path/to/install/a', false),
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for vendorB/packageB</info>'),
            Phake::verify($this->client)->install('/path/to/install/b', false)
        );
    }

    public function testUpdateNothing()
    {
        $this->rootPackage->setRequires(array($this->linkRoot1, $this->linkRoot2));
        Phake::when($this->vendorFinder)->find($this->composer, $this->bridge)->thenReturn(array());
        $this->bridge->update($this->composer);

        Phake::inOrder(
            Phake::verify($this->io)->write('<info>Updating NPM dependencies for root project</info>'),
            Phake::verify($this->io)->write('Nothing to update'),
            Phake::verify($this->io)->write('<info>Installing NPM dependencies for Composer dependencies</info>'),
            Phake::verify($this->io)->write('Nothing to install')
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
