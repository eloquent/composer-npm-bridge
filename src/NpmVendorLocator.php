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
use Composer\Package\PackageInterface;

/**
 * Locates NPM bridge enabled vendors.
 */
class NpmVendorLocator implements NpmVendorLocatorInterface
{
    /**
     * Find all NPM bridge enabled vendors and return their locations.
     *
     * @param Composer $composer The Composer object for the root project.
     *
     * @return array<integer,string> The list of NPM bridge enabled vendor paths.
     */
    public function find(Composer $composer)
    {
        $packages = $composer->getRepositoryManager()->getLocalRepository()
            ->getPackages();
        $vendorDir = $composer->getConfig()->get('vendor-dir');

        $paths = array();
        foreach ($packages as $package) {
            if ($this->packageRequiresNpmBridge($package)) {
                $paths[] = sprintf('%s/%s', $vendorDir, $package->getName());
            }
        }

        return $paths;
    }

    /**
     * Returns true if the supplied package requires the Composer NPM bridge.
     *
     * @param PackageInterface $package The package to inspect.
     *
     * @return boolean True if the package requires the bridge.
     */
    protected function packageRequiresNpmBridge(PackageInterface $package)
    {
        foreach ($package->getRequires() as $link) {
            if ('eloquent/composer-npm-bridge' === $link->getTarget()) {
                return true;
            }
        }

        return false;
    }
}
