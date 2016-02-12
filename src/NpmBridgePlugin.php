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
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Eloquent\Composer\NpmBridge\Exception\NpmCommandFailedException;

/**
 * A Composer plugin to facilitate NPM integration.
 */
class NpmBridgePlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * Construct a new Composer NPM bridge plugin.
     *
     * @param NpmBridgeFactory|null $bridgeFactory The bridge factory to use.
     */
    public function __construct(NpmBridgeFactory $bridgeFactory = null)
    {
        if (null === $bridgeFactory) {
            $bridgeFactory = NpmBridgeFactory::create();
        }

        $this->bridgeFactory = $bridgeFactory;
    }

    /**
     * Activate the plugin.
     *
     * @param Composer    $composer The main Composer object.
     * @param IOInterface $io       The i/o interface to use.
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // no action required
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
     *
     * @throws NpmNotFoundException      If the npm executable cannot be located.
     * @throws NpmCommandFailedException If the operation fails.
     */
    public function onPostInstallCmd(Event $event)
    {
        $this->bridgeFactory->createBridge($event->getIO())
            ->install($event->getComposer(), $event->isDevMode());
    }

    /**
     * Handle post update command events.
     *
     * @param Event $event The event to handle.
     *
     * @throws NpmNotFoundException      If the npm executable cannot be located.
     * @throws NpmCommandFailedException If the operation fails.
     */
    public function onPostUpdateCmd(Event $event)
    {
        $this->bridgeFactory->createBridge($event->getIO())
            ->update($event->getComposer());
    }

    private $bridgeFactory;
}
