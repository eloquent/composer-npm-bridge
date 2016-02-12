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
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Eloquent\Composer\NpmBridge\Exception\NpmCommandFailedException;
use Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException;

/**
 * Manages NPM installs, updates, and shrinkwrapping for Composer projects.
 */
class NpmBridge
{
    /**
     * Construct a new Composer NPM bridge plugin.
     *
     * @access private
     *
     * @param IOInterface     $io           The i/o interface to use.
     * @param NpmVendorFinder $vendorFinder The vendor finder to use.
     * @param NpmClient       $client       The NPM client to use.
     */
    public function __construct(
        IOInterface $io,
        NpmVendorFinder $vendorFinder,
        NpmClient $client
    ) {
        $this->io = $io;
        $this->vendorFinder = $vendorFinder;
        $this->client = $client;
    }

    /**
     * Install NPM dependencies for a Composer project and its dependencies.
     *
     * @param Composer $composer  The main Composer object.
     * @param boolean  $isDevMode True if dev mode is enabled.
     *
     * @throws NpmNotFoundException      If the npm executable cannot be located.
     * @throws NpmCommandFailedException If the operation fails.
     */
    public function install(Composer $composer, $isDevMode = true)
    {
        $this->io->write(
            '<info>Installing NPM dependencies for root project</info>'
        );

        if ($this->isDependantPackage($composer->getPackage(), $isDevMode)) {
            $this->client->install(null, $isDevMode);
        } else {
            $this->io->write('Nothing to install');
        }

        $this->installForVendors($composer);
    }

    /**
     * Update NPM dependencies for a Composer project and its dependencies.
     *
     * This will update and shrinkwrap the NPM dependencies of the main project.
     * It will also install any NPM dependencies of the main project's Composer
     * dependencies.
     *
     * @param Composer $composer The main Composer object.
     *
     * @throws NpmNotFoundException      If the npm executable cannot be located.
     * @throws NpmCommandFailedException If the operation fails.
     */
    public function update(Composer $composer)
    {
        $this->io->write(
            '<info>Updating NPM dependencies for root project</info>'
        );

        if ($this->isDependantPackage($composer->getPackage(), true)) {
            $this->client->update();
            $this->client->install(null, true);
            $this->client->shrinkwrap();
        } else {
            $this->io->write('Nothing to update');
        }

        $this->installForVendors($composer);
    }

    /**
     * Returns true if the supplied package requires the Composer NPM bridge.
     *
     * @param PackageInterface $package                The package to inspect.
     * @param boolean          $includeDevDependencies True if the dev dependencies should also be inspected.
     *
     * @return boolean True if the package requires the bridge.
     */
    public function isDependantPackage(
        PackageInterface $package,
        $includeDevDependencies = false
    ) {
        foreach ($package->getRequires() as $link) {
            if ('eloquent/composer-npm-bridge' === $link->getTarget()) {
                return true;
            }
        }

        if ($includeDevDependencies) {
            foreach ($package->getDevRequires() as $link) {
                if ('eloquent/composer-npm-bridge' === $link->getTarget()) {
                    return true;
                }
            }
        }

        return false;
    }

    private function installForVendors($composer)
    {
        $this->io->write(
            '<info>Installing NPM dependencies for Composer dependencies</info>'
        );

        $packages = $this->vendorFinder->find($composer, $this);

        if (count($packages) > 0) {
            foreach ($packages as $package) {
                $this->io->write(
                    sprintf(
                        '<info>Installing NPM dependencies for %s</info>',
                        $package->getPrettyName()
                    )
                );

                $this->client->install(
                    $composer->getInstallationManager()
                        ->getInstallPath($package),
                    false
                );
            }
        } else {
            $this->io->write('Nothing to install');
        }
    }

    private $io;
    private $vendorFinder;
    private $client;
}
