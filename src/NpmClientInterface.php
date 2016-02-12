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

/**
 * The interface implemented by NPM clients.
 */
interface NpmClientInterface
{
    /**
     * Install NPM dependencies for the project at the supplied path.
     *
     * @param string|null  $path      The path to the NPM project, or null to use the current working directory.
     * @param boolean|null $isDevMode True if dev dependencies should be included.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function install($path = null, $isDevMode = null);

    /**
     * Update NPM dependencies for the project at the supplied path.
     *
     * @param string|null $path The path to the NPM project, or null to use the current working directory.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function update($path = null);

    /**
     * Shrink-wrap NPM dependencies for the project at the supplied path.
     *
     * @param string|null $path The path to the NPM project, or null to use the current working directory.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function shrinkwrap($path = null);
}
