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
use Composer\Script\ScriptEvents;

/**
 * A Composer plugin to facilitate NPM integration.
 */
class NpmBridgePlugin implements PluginInterface, EventSubscriberInterface
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

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     * * The method name to call (priority defaults to 0)
     * * An array composed of the method name to call and the priority
     * * An array of arrays composed of the method names to call and respective
     *   priorities, or 0 if unset
     *
     * For instance:
     *
     * * array('eventName' => 'methodName')
     * * array('eventName' => array('methodName', $priority))
     * * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => 'onPostInstallCmd',
            ScriptEvents::POST_UPDATE_CMD => 'onPostUpdateCmd',
            ScriptEvents::POST_PACKAGE_INSTALL => 'onPostPackageInstall',
            ScriptEvents::POST_PACKAGE_UPDATE => 'onPostPackageUpdate',
            ScriptEvents::POST_ROOT_PACKAGE_INSTALL => 'onPostRootPackageInstall',
        );
    }

    public function onPostInstallCmd()
    {
        var_dump(__METHOD__, __FUNCTION__);
    }

    public function onPostUpdateCmd()
    {
        var_dump(__METHOD__, __FUNCTION__);
    }

    public function onPostPackageInstall()
    {
        var_dump(__METHOD__, __FUNCTION__);
    }

    public function onPostPackageUpdate()
    {
        var_dump(__METHOD__, __FUNCTION__);
    }

    public function onPostRootPackageInstall()
    {
        var_dump(__METHOD__, __FUNCTION__);
    }
}
