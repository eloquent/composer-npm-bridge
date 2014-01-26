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

/**
 * The interface implemented by NPM vendor locators.
 */
interface NpmVendorLocatorInterface
{
    /**
     * Find all NPM bridge enabled vendors and return their locations.
     *
     * @param Composer $composer The Composer object for the root project.
     *
     * @return array<integer,string> The list of NPM bridge enabled vendor paths.
     */
    public function find(Composer $composer);
}
