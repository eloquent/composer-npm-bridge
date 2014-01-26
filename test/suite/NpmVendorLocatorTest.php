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
use Composer\Config;
use Composer\Package\Link;
use Composer\Package\Package;
use Composer\Repository\ArrayRepository;
use PHPUnit_Framework_TestCase;
use Phake;

class NpmVendorLocatorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->locator = new NpmVendorLocator;

        $this->composer = new Composer;
        $this->repositoryManager = Phake::mock('Composer\Repository\RepositoryManager');
        $this->localRepository = new ArrayRepository;
        $this->config = new Config;

        $this->packageA = new Package('vendorA/packageA', '1.0.0.0', '1.0.0');
        $this->packageB = new Package('vendorB/packageB', '1.0.0.0', '1.0.0');
        $this->packageC = new Package('vendorC/packageC', '1.0.0.0', '1.0.0');
        $this->packageD = new Package('vendorD/packageD', '1.0.0.0', '1.0.0');

        $this->linkA1 = new Link('vendorA/packageA', 'vendorX/packageX');
        $this->linkA2 = new Link('vendorA/packageA', 'vendorY/packageY');
        $this->linkB1 = new Link('vendorB/packageB', 'vendorZ/packageZ');
        $this->linkB2 = new Link('vendorB/packageB', 'eloquent/composer-npm-bridge');
        $this->linkC1 = new Link('vendorC/packageC', 'vendorZ/packageZ');
        $this->linkC2 = new Link('vendorC/packageC', 'eloquent/composer-npm-bridge');
        $this->linkD1 = new Link('vendorD/packageD', 'eloquent/composer-npm-bridge');
        $this->linkD2 = new Link('vendorD/packageD', 'vendorZ/packageZ');

        $this->composer->setRepositoryManager($this->repositoryManager);
        Phake::when($this->repositoryManager)->getLocalRepository()->thenReturn($this->localRepository);
        $this->composer->setConfig($this->config);

        $this->localRepository->addPackage($this->packageA);
        $this->localRepository->addPackage($this->packageB);
        $this->localRepository->addPackage($this->packageC);
        $this->localRepository->addPackage($this->packageD);

        $this->packageA->setRequires(array($this->linkA1, $this->linkA2));
        $this->packageB->setRequires(array($this->linkB1, $this->linkB2));
        $this->packageC->setDevRequires(array($this->linkC1, $this->linkC2));
        $this->packageD->setRequires(array($this->linkD1, $this->linkD2));

        $this->config->merge(array('vendor-dir' => 'path/to/vendor'));
    }

    public function testFind()
    {
        $expected = array(
            'vendor/vendorb/packageb',
            'vendor/vendord/packaged',
        );

        $this->assertSame($expected, $this->locator->find($this->composer));
    }
}
