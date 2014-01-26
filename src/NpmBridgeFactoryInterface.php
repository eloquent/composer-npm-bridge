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

use Composer\IO\IOInterface;

/**
 * The interface implemented by NPM bridge factories.
 */
interface NpmBridgeFactoryInterface
{
    /**
     * Construct a new Composer NPM bridge plugin.
     *
     * @param IOInterface|null               $io            The i/o interface to use.
     * @param NpmVendorLocatorInterface|null $vendorLocator The vendor locator to use.
     * @param NpmClientInterface|null        $client        The NPM client to use.
     */
    public function create(
        IOInterface $io = null,
        NpmVendorLocatorInterface $vendorLocator = null,
        NpmClientInterface $client = null
    );
}
