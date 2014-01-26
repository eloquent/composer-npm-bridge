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

/**
 * The interface implemented by NPM clients.
 */
interface NpmClientInterface
{
    /**
     * Install NPM dependencies for the project at the supplied path.
     *
     * @param string $path The path to the NPM project.
     *
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function install($path);

    /**
     * Update NPM dependencies for the project at the supplied path.
     *
     * @param string $path The path to the NPM project.
     *
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function update($path);

    /**
     * Shrink-wrap NPM dependencies for the project at the supplied path.
     *
     * @param string $path The path to the NPM project.
     *
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function shrinkwrap($path);
}
