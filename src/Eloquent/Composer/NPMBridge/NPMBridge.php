<?php

/*
 * This file is part of the Composer NPM bridge package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Composer\NPMBridge;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Icecave\Isolator\Isolator;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

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
     * @param ExecutableFinder|null $executableFinder
     * @param Isolator|null $isolator
     */
    public function __construct(
        ExecutableFinder $executableFinder = null,
        Isolator $isolator = null
    ) {
        if (null === $executableFinder) {
            $executableFinder = new ExecutableFinder;
        }

        $this->executableFinder = $executableFinder;
        $this->isolator = Isolator::get($isolator);
    }

    /**
     * @return ExecutableFinder
     */
    public function executableFinder()
    {
        return $this->executableFinder;
    }

    /**
     * @param Event $event
     */
    public function postInstall(Event $event)
    {
        $io = $event->getIO();

        $io->write('<info>Installing NPM dependencies</info>');
        $this->executeNpm(array('install'), $io);

        $io->write('<info>Shrinkwrapping NPM modules</info>');
        $this->executeNpm(array('shrinkwrap'), $io);
    }

    /**
     * @param Event $event
     */
    public function postUpdate(Event $event)
    {
        $io = $event->getIO();

        $this->unwrap($io);

        $io->write('<info>Updating NPM dependencies</info>');
        $this->executeNpm(array('update'), $io);

        $io->write('<info>Shrinkwrapping NPM modules</info>');
        $this->executeNpm(array('shrinkwrap'), $io);
    }

    /**
     * @param array<string> $arguments
     * @param IOInterface $io
     */
    protected function executeNpm(array $arguments, IOInterface $io)
    {
        $npmProcess = $this->createNpmProcess($arguments);
        $isolator = $this->isolator;
        $npmProcess->run(function ($type, $buffer) use ($isolator, $io) {
            if (Process::ERR === $type) {
                $isolator->fwrite(STDERR, $buffer);
            } else {
                $io->write($buffer, false);
            }
        });
    }

    /**
     * @param IOInterface $io
     */
    protected function unwrap(IOInterface $io)
    {
        $io->write('<info>Removing NPM shrinkwrap</info>');
        if ($this->isolator->is_file('./npm-shrinkwrap.json')) {
            $this->isolator->unlink('./npm-shrinkwrap.json');
            $io->write('<info>NPM shrinkwrap removed</info>');
        } else {
            $io->write('NPM shrinkwrap not found');
        }
    }

    /**
     * @param array<string> $arguments
     *
     * @return Process
     */
    protected function createNpmProcess(array $arguments)
    {
        array_unshift($arguments, $this->npmPath());
        $processBuilder = $this->createProcessBuilder($arguments);

        return $processBuilder->getProcess();
    }

    /**
     * @return string
     */
    protected function npmPath()
    {
        if (null === $this->npmPath) {
            $this->npmPath = $this->executableFinder()->find('npm');
            if (null === $this->npmPath) {
                throw new RuntimeException('Unable to locate npm executable.');
            }
        }

        return $this->npmPath;
    }

    /**
     * @param array<string> $arguments
     *
     * @return ProcessBuilder
     */
    protected function createProcessBuilder(array $arguments)
    {
        return new ProcessBuilder($arguments);
    }

    private $executableFinder;
    private $isolator;
    private $npmPath;
}
