<?php

/*
 * This file is part of the Composer NPM bridge package.
 *
 * Copyright © 2012 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Composer\NPMBridge;

use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class NPMBridge
{
    /**
     * @param NPMBridge|null $instance
     *
     * @return NPMBridge
     */
    public static function get(NPMBridge $instance = null)
    {
        if (null === $instance) {
            $instance = new NPMBridge;
        }

        return $instance;
    }

    /**
     * @param Event $event
     * @param NPMBridge|null $instance
     */
    public static function handle(
        Event $event,
        NPMBridge $instance = null
    ) {
        $instance = static::get($instance);

        switch ($event->getName()) {
            case ScriptEvents::POST_INSTALL_CMD:
                $instance->postInstall($event);
                break;
            case ScriptEvents::POST_UPDATE_CMD:
                $instance->postUpdate($event);
        }
    }

    /**
     * @param Event $event
     */
    public function postInstall(Event $event)
    {
    }

    /**
     * @param Event $event
     */
    public function postUpdate(Event $event)
    {
    }
}
