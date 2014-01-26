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

use Composer\Util\ProcessExecutor;
use Symfony\Component\Process\ExecutableFinder;

/**
 * A simple client for performing NPM operations.
 */
class NpmClient implements NpmClientInterface
{
    /**
     * Construct a new NPM client.
     *
     * @param ProcessExecutor|null  $processExecutor  The process executor to use.
     * @param ExecutableFinder|null $executableFinder The executable finder to use.
     */
    public function __construct(
        ProcessExecutor $processExecutor = null,
        ExecutableFinder $executableFinder = null
    ) {
        if (null === $processExecutor) {
            $processExecutor = new ProcessExecutor;
        }
        if (null === $executableFinder) {
            $executableFinder = new ExecutableFinder;
        }

        $this->processExecutor = $processExecutor;
        $this->executableFinder = $executableFinder;
    }

    /**
     * Get the process executor.
     *
     * @return ProcessExecutor The process executor.
     */
    public function processExecutor()
    {
        return $this->processExecutor;
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
     * Install NPM dependencies for the project at the supplied path.
     *
     * @param string $path The path to the NPM project.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function install($path)
    {
        $this->executeNpm(array('install'), $path);
    }

    /**
     * Update NPM dependencies for the project at the supplied path.
     *
     * @param string $path The path to the NPM project.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function update($path)
    {
        $this->executeNpm(array('update'), $path);
    }

    /**
     * Shrink-wrap NPM dependencies for the project at the supplied path.
     *
     * @param string $path The path to the NPM project.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function shrinkwrap($path)
    {
        $this->executeNpm(array('shrinkwrap'), $path);
    }

    /**
     * Execute an NPM command.
     *
     * @param array<integer,string> $arguments            The arguments to pass to the npm executable.
     * @param string|null           $workingDirectoryPath The path to the working directory, or null to use the current working directory.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    protected function executeNpm(
        array $arguments,
        $workingDirectoryPath = null
    ) {
        array_unshift($arguments, $this->npmPath());
        $command = implode(' ', array_map('escapeshellarg', $arguments));

        $output = null;
        $exitCode = $this->processExecutor()
            ->execute($command, $output, $workingDirectoryPath);

        if (0 !== $exitCode) {
            throw new Exception\NpmCommandFailedException($command);
        }
    }

    /**
     * Get the npm exectable path.
     *
     * @return string                         The path to the npm executable.
     * @throws Exception\NpmNotFoundException If the npm executable cannot be located.
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

    private $processExecutor;
    private $executableFinder;
    private $npmPath;
}
