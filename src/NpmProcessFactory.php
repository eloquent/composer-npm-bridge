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

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Creates NPM processes.
 */
class NpmProcessFactory implements NpmProcessFactoryInterface
{
    /**
     * Construct a new NPM process factory.
     *
     * @param ExecutableFinder|null $executableFinder The executable finder to use.
     */
    public function __construct(ExecutableFinder $executableFinder = null)
    {
        if (null === $executableFinder) {
            $executableFinder = new ExecutableFinder;
        }

        $this->executableFinder = $executableFinder;
    }

    /**
     * Get the executable finder.
     *
     * @return ExecutableFinder The executable finder.
     */
    public function executableFinder()
    {
        return $this->executableFinder;
    }

    /**
     * Create a new NPM process.
     *
     * @param array<integer,string> $arguments The arguments to pass to NPM.
     *
     * @return Process The newly created NPM process.
     * @throws Exception\NpmNotFoundException If the npm executable cannot be found.
     */
    public function create(array $arguments)
    {
        array_unshift($arguments, $this->npmPath());
        $processBuilder = $this->createProcessBuilder($arguments);

        return $processBuilder->getProcess();
    }

    /**
     * Get the path to the npm executable.
     *
     * @return string The npm executable path.
     * @throws Exception\NpmNotFoundException If the npm executable cannot be found.
     */
    protected function npmPath()
    {
        if (null === $this->npmPath) {
            $this->npmPath = $this->executableFinder()->find('npm');
            if (null === $this->npmPath) {
                throw new Exception\NpmNotFoundException;
            }
        }

        return $this->npmPath;
    }

    /**
     * Create a new Symfony process builder.
     *
     * @param array<integer,string> $arguments The arguments to pass to the process.
     *
     * @return ProcessBuilder The newly created process builder.
     */
    protected function createProcessBuilder(array $arguments)
    {
        return new ProcessBuilder($arguments);
    }

    private $executableFinder;
    private $npmPath;
}
