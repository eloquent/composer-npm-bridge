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
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * A Composer plugin to facilitate NPM integration.
 */
class NpmBridgePlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * Activate the plugin.
     *
     * @param Composer    $composer The main Composer object.
     * @param IOInterface $io       The i/o interface to use.
     */
    public function activate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * Get the event subscriber configuration for this plugin.
     *
     * @return array<string,string> The events to listen to, and their associated handlers.
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => 'onPostInstallCmd',
            ScriptEvents::POST_UPDATE_CMD => 'onPostUpdateCmd',
        );
    }

    /**
     * Handle post install command events.
     *
     * @param Event $event The event to handle.
     */
    public function onPostInstallCmd(Event $event)
    {
        var_dump(__METHOD__, __FUNCTION__);
    }

    /**
     * Handle post update command events.
     *
     * @param Event $event The event to handle.
     */
    public function onPostUpdateCmd(Event $event)
    {
        var_dump(__METHOD__, __FUNCTION__);
    }
}
