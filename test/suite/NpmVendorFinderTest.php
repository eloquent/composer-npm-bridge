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
use Composer\IO\NullIO;
use Composer\Package\Link;
use Composer\Package\Package;
use Composer\Repository\ArrayRepository;
use Eloquent\Phony\Phpunit\Phony;
use PHPUnit_Framework_TestCase;

class NpmVendorFinderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->finder = new NpmVendorFinder();

        $this->composer = new Composer();
        $this->repositoryManager = Phony::mock('Composer\Repository\RepositoryManager');
        $this->localRepository = new ArrayRepository();

        $this->bridge = NpmBridgeFactory::create()->createBridge(new NullIO());

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

        $this->composer->setRepositoryManager($this->repositoryManager->mock());
        $this->repositoryManager->getLocalRepository->returns($this->localRepository);

        $this->localRepository->addPackage($this->packageA);
        $this->localRepository->addPackage($this->packageB);
        $this->localRepository->addPackage($this->packageC);
        $this->localRepository->addPackage($this->packageD);

        $this->packageA->setRequires(array($this->linkA1, $this->linkA2));
        $this->packageB->setRequires(array($this->linkB1, $this->linkB2));
        $this->packageC->setDevRequires(array($this->linkC1, $this->linkC2));
        $this->packageD->setRequires(array($this->linkD1, $this->linkD2));
    }

    public function testFind()
    {
        $this->assertSame(array($this->packageB, $this->packageD), $this->finder->find($this->composer, $this->bridge));
    }
}
