<?php

namespace Eloquent\Composer\NpmBridge;

use Composer\Composer;
use Composer\Package\PackageInterface;

/**
 * Finds NPM bridge enabled vendor packages.
 */
class NpmVendorFinder
{
    /**
     * Find all NPM bridge enabled vendor packages.
     *
     * @param Composer  $composer The Composer object for the root project.
     * @param NpmBridge $bridge   The bridge to use.
     *
     * @return array<integer,PackageInterface> The list of NPM bridge enabled vendor packages.
     */
    public function find(Composer $composer, NpmBridge $bridge): array
    {
        $packages = $composer->getRepositoryManager()->getLocalRepository()
            ->getPackages();

        $dependantPackages = [];

        foreach ($packages as $package) {
            if ($bridge->isDependantPackage($package, false)) {
                $dependantPackages[] = $package;
            }
        }

        return $dependantPackages;
    }
}
