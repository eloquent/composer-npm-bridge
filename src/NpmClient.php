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

/**
 * A simple client for performing NPM operations.
 */
class NpmClient implements NpmClientInterface
{
    /**
     * Construct a new NPM client.
     *
     * @param NpmProcessFactoryInterface|null $processFactory The NPM process factory to use.
     */
    public function __construct(
        NpmProcessFactoryInterface $processFactory = null
    ) {
        if (null === $processFactory) {
            $processFactory = new NpmProcessFactory;
        }

        $this->processFactory = $processFactory;
    }

    /**
     * Get the process factory.
     *
     * @return NpmProcessFactoryInterface The process factory.
     */
    public function processFactory()
    {
        return $this->processFactory;
    }

    /**
     * Install NPM dependencies for the project at the supplied path.
     *
     * @param string $path The path to the NPM project.
     *
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function install($path)
    {

    }

    /**
     * Update NPM dependencies for the project at the supplied path.
     *
     * @param string $path The path to the NPM project.
     *
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function update($path)
    {
    }

    /**
     * Shrink-wrap NPM dependencies for the project at the supplied path.
     *
     * @param string $path The path to the NPM project.
     *
     * @throws Exception\NpmCommandFailedException If the operation fails.
     */
    public function shrinkwrap($path)
    {
    }

    private $processFactory;
}
