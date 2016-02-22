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

use Composer\Util\ProcessExecutor;
use Eloquent\Composer\NpmBridge\Exception\NpmCommandFailedException;
use Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException;
use Symfony\Component\Process\ExecutableFinder;

/**
 * A simple client for performing NPM operations.
 */
class NpmClient
{
    /**
     * Create a new NPM client.
     *
     * @return self The newly created client.
     */
    public static function create()
    {
        return new self(new ProcessExecutor(), new ExecutableFinder());
    }

    /**
     * Construct a new NPM client.
     *
     * @access private
     *
     * @param ProcessExecutor  $processExecutor  The process executor to use.
     * @param ExecutableFinder $executableFinder The executable finder to use.
     * @param callable         $getcwd           The getcwd() implementation to use.
     * @param callable         $chdir            The chdir() implementation to use.
     */
    public function __construct(
        ProcessExecutor $processExecutor,
        ExecutableFinder $executableFinder,
        $getcwd = 'getcwd',
        $chdir = 'chdir'
    ) {
        $this->processExecutor = $processExecutor;
        $this->executableFinder = $executableFinder;
        $this->getcwd = $getcwd;
        $this->chdir = $chdir;
    }

    /**
     * Install NPM dependencies for the project at the supplied path.
     *
     * @param string|null  $path      The path to the NPM project, or null to use the current working directory.
     * @param boolean|null $isDevMode True if dev dependencies should be included.
     *
     * @throws NpmNotFoundException      If the npm executable cannot be located.
     * @throws NpmCommandFailedException If the operation fails.
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
     * @throws NpmNotFoundException      If the npm executable cannot be located.
     * @throws NpmCommandFailedException If the operation fails.
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
     * @throws NpmNotFoundException      If the npm executable cannot be located.
     * @throws NpmCommandFailedException If the operation fails.
     */
    public function shrinkwrap($path = null)
    {
        $this->executeNpm(array('shrinkwrap'), $path);
    }

    private function executeNpm($arguments, $workingDirectoryPath)
    {
        array_unshift($arguments, $this->npmPath());
        $command = implode(' ', array_map('escapeshellarg', $arguments));

        if (null !== $workingDirectoryPath) {
            $previousWorkingDirectoryPath = call_user_func($this->getcwd);
            call_user_func($this->chdir, $workingDirectoryPath);
        }

        $exitCode = $this->processExecutor->execute($command);

        if (null !== $workingDirectoryPath) {
            call_user_func($this->chdir, $previousWorkingDirectoryPath);
        }

        if (0 !== $exitCode) {
            throw new NpmCommandFailedException($command);
        }
    }

    private function npmPath()
    {
        if (null === $this->npmPath) {
            $this->npmPath = $this->executableFinder->find('npm');

            if (null === $this->npmPath) {
                throw new NpmNotFoundException();
            }
        }

        return $this->npmPath;
    }

    private $processExecutor;
    private $executableFinder;
    private $getcwd;
    private $chdir;
    private $npmPath;
}
