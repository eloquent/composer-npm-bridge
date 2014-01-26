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
use Composer\Plugin\PluginInterface;

/**
 * A Composer plugin to facilitate NPM integration.
 */
class NpmBridgePlugin implements PluginInterface
{
    /**
     * Activate the plugin.
     *
     * @param Composer $composer The main Composer object.
     * @param IOInterface $io The IO interface to use.
     */
    public function activate(Composer $composer, IOInterface $io)
    {
    }
}
