<?php

namespace Eloquent\Composer\NpmBridge;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Eloquent\Composer\NpmBridge\Exception\NpmCommandFailedException;
use Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException;

/**
 * Manages NPM installs, and updates for Composer projects.
 */
class NpmBridge
{
    const EXTRA_KEY = 'npm-bridge';
    const EXTRA_KEY_OPTIONAL = 'optional';
    const EXTRA_KEY_TIMEOUT = 'timeout';

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
     * @param bool     $isDevMode True if dev mode is enabled.
     *
     * @throws NpmNotFoundException      If the npm executable cannot be located.
     * @throws NpmCommandFailedException If the operation fails.
     */
    public function install(Composer $composer, bool $isDevMode = true)
    {
        $this->io->write(
            '<info>Installing NPM dependencies for root project</info>'
        );

        $package = $composer->getPackage();
        if ($this->isDependantPackage($package, $isDevMode)) {
            if (!$this->shouldSkipPackage($package)) {
                $this->configureClient($package);
                $this->client->install(null, $isDevMode);
            }
        } else {
            $this->io->write('Nothing to install');
        }

        $this->installForVendors($composer);
    }

    /**
     * Update NPM dependencies for a Composer project and its dependencies.
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

        $package = $composer->getPackage();
        if ($this->isDependantPackage($package, true)) {
            if (!$this->shouldSkipPackage($package)) {
                $this->configureClient($package);
                $this->client->update();
                $this->client->install(null, true);
            }
        } else {
            $this->io->write('Nothing to update');
        }

        $this->installForVendors($composer);
    }

    /**
     * Returns true if the supplied package requires the Composer NPM bridge.
     *
     * @param PackageInterface $package                The package to inspect.
     * @param bool             $includeDevDependencies True if the dev dependencies should also be inspected.
     *
     * @return bool True if the package requires the bridge.
     */
    public function isDependantPackage(
        PackageInterface $package,
        bool $includeDevDependencies = false
    ): bool {
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
                if ($this->shouldSkipPackage($package)) {
                    continue;
                }

                $this->io->write(
                    sprintf(
                        '<info>Installing NPM dependencies for %s</info>',
                        $package->getPrettyName()
                    )
                );

                $this->configureClient($package);
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

    private function configureClient(PackageInterface $package)
    {
        $extra = $package->getExtra();
        // Issue #13 - npm can take a while, so allow a custom timeout
        if (isset($extra[self::EXTRA_KEY][self::EXTRA_KEY_TIMEOUT])) {
            $this->client->setTimeout(intval($extra[self::EXTRA_KEY][self::EXTRA_KEY_TIMEOUT]));
        } else {
            $this->client->setTimeout(null);
        }
    }

    private function shouldSkipPackage(PackageInterface $package)
    {
        if ($this->client->valid()) {
            return false;
        }

        $extra = $package->getExtra();
        if (!empty($extra[self::EXTRA_KEY][self::EXTRA_KEY_OPTIONAL])) {
            $this->io->write(
                sprintf(
                    '<info>Skipping optional NPM dependencies for %s as npm is unavailable</info>',
                    $package->getPrettyName()
                )
            );

            return true;
        }

        return false;
    }

    private $io;
    private $vendorFinder;
    private $client;
}
