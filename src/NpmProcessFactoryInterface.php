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

use Symfony\Component\Process\Process;

/**
 * The interface implemented by NPM process factories.
 */
interface NpmProcessFactoryInterface
{
    /**
     * Create a new NPM process.
     *
     * @param array<integer,string> $arguments The arguments to pass to NPM.
     *
     * @return Process The newly created NPM process.
     * @throws Exception\NpmNotFoundException If the npm executable cannot be found.
     */
    public function create(array $arguments);
}
