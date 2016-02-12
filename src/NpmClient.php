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
use Icecave\Isolator\Isolator;
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
     * @param Isolator|null         $isolator         The isolator to use.
     */
    public function __construct(
        ProcessExecutor $processExecutor = null,
        ExecutableFinder $executableFinder = null,
        Isolator $isolator = null
    ) {
        if (null === $processExecutor) {
            $processExecutor = new ProcessExecutor();
        }
        if (null === $executableFinder) {
            $executableFinder = new ExecutableFinder();
        }

        $this->processExecutor = $processExecutor;
        $this->executableFinder = $executableFinder;
        $this->isolator = Isolator::get($isolator);
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
     * @param string|null  $path      The path to the NPM project, or null to use the current working directory.
     * @param boolean|null $isDevMode True if dev dependencies should be included.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function install($path = null, $isDevMode = null)
    {
        if (null === $isDevMode) {
            $isDevMode = true;
        }

        if ($isDevMode) {
            $arguments = array('install');
        } else {
            $arguments = array('install', '--production');
        }

        $this->executeNpm($arguments, $path);
    }

    /**
     * Update NPM dependencies for the project at the supplied path.
     *
     * @param string|null $path The path to the NPM project, or null to use the current working directory.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function update($path = null)
    {
        $this->executeNpm(array('update'), $path);
    }

    /**
     * Shrink-wrap NPM dependencies for the project at the supplied path.
     *
     * @param string|null $path The path to the NPM project, or null to use the current working directory.
     *
     * @throws Exception\NpmNotFoundException      If the npm executable cannot be located.
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function shrinkwrap($path = null)
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

        if (null !== $workingDirectoryPath) {
            $previousWorkingDirectoryPath = $this->isolator()->getcwd();
            $this->isolator()->chdir($workingDirectoryPath);
        }

        $exitCode = $this->processExecutor()->execute($command);

        if (null !== $workingDirectoryPath) {
            $this->isolator()->chdir($previousWorkingDirectoryPath);
        }

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
                throw new Exception\NpmNotFoundException();
            }
        }

        return $this->npmPath;
    }

    /**
     * Get the isolator.
     *
     * @return Isolator The isolator.
     */
    protected function isolator()
    {
        return $this->isolator;
    }

    private $processExecutor;
    private $executableFinder;
    private $isolator;
    private $npmPath;
}
