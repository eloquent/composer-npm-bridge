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
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Package\PackageInterface;
use Composer\Util\ProcessExecutor;

/**
 * Manages NPM installs, updates, and shrinkwrapping for Composer projects.
 */
class NpmBridge implements NpmBridgeInterface
{
    /**
     * Construct a new Composer NPM bridge plugin.
     *
     * @param IOInterface|null              $io           The i/o interface to use.
     * @param NpmVendorFinderInterface|null $vendorFinder The vendor finder to use.
     * @param NpmClientInterface|null       $client       The NPM client to use.
     */
    public function __construct(
        IOInterface $io = null,
        NpmVendorFinderInterface $vendorFinder = null,
        NpmClientInterface $client = null
    ) {
        if (null === $io) {
            $io = new NullIO;
        }
        if (null === $vendorFinder) {
            $vendorFinder = new NpmVendorFinder;
        }
        if (null === $client) {
            $client = new NpmClient(new ProcessExecutor($io));
        }

        $this->io = $io;
        $this->vendorFinder = $vendorFinder;
        $this->client = $client;
    }

    /**
     * Get the i/o interface.
     *
     * @return IOInterface The i/o interface.
     */
    public function io()
    {
        return $this->io;
    }

    /**
     * Get the vendor finder.
     *
     * @return NpmVendorFinderInterface The vendor finder.
     */
    public function vendorFinder()
    {
        return $this->vendorFinder;
    }

    /**
     * Get the NPM client.
     *
     * @return NpmClientInterface The NPM client.
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * Install NPM dependencies for a Composer project and its dependencies.
     *
     * @param Composer $composer The main Composer object.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function install(Composer $composer)
    {
        $this->io()->write(
            '<info>Installing NPM dependencies for root project</info>'
        );

        if ($this->isDependantPackage($composer->getPackage())) {
            $this->client()->install();
        } else {
            $this->io()->write('Nothing to install');
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
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function update(Composer $composer)
    {
        $this->io()->write(
            '<info>Updating NPM dependencies for root project</info>'
        );

        if ($this->isDependantPackage($composer->getPackage())) {
            $this->client()->update();
            $this->client()->shrinkwrap();
        } else {
            $this->io()->write('Nothing to update');
        }

        $this->installForVendors($composer);
    }

    /**
     * Returns true if the supplied package requires the Composer NPM bridge.
     *
     * @param PackageInterface $package The package to inspect.
     *
     * @return boolean True if the package requires the bridge.
     */
    public function isDependantPackage(PackageInterface $package)
    {
        foreach ($package->getRequires() as $link) {
            if ('eloquent/composer-npm-bridge' === $link->getTarget()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Install NPM dependencies for all Composer dependencies that use the
     * bridge.
     *
     * @param Composer $composer The main Composer object.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    protected function installForVendors(Composer $composer)
    {
        $this->io()->write(
            '<info>Installing NPM dependencies for Composer dependencies</info>'
        );

        $packages = $this->vendorFinder()->find($composer, $this);
        if (count($packages) > 0) {
            $vendorDir = $composer->getConfig()->get('vendor-dir');

            foreach ($packages as $package) {
                $this->io()->write(
                    sprintf(
                        '<info>Installing NPM dependencies for %s</info>',
                        $package->getPrettyName()
                    )
                );

                $this->client()->install(
                    sprintf('%s/%s', $vendorDir, $package->getName())
                );
            }
        } else {
            $this->io()->write('Nothing to install');
        }
    }

    private $io;
    private $vendorFinder;
    private $client;
}
