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
 * The interface implemented by NPM bridges.
 */
interface NpmBridgeInterface
{
    /**
     * Install NPM dependencies for a Composer project and its dependencies.
     *
     * @param Composer     $composer  The main Composer object.
     * @param boolean|null $isDevMode True if dev mode is enabled.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function install(Composer $composer, $isDevMode = null);

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
    public function update(Composer $composer);

    /**
     * Returns true if the supplied package requires the Composer NPM bridge.
     *
     * @param PackageInterface $package                The package to inspect.
     * @param boolean|null     $includeDevDependencies True if the dev dependencies should also be inspected.
     *
     * @return boolean True if the package requires the bridge.
     */
    public function isDependantPackage(
        PackageInterface $package,
        $includeDevDependencies = null
    );
}
