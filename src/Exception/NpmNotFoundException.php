<?php

namespace Eloquent\Composer\NpmBridge\Exception;

use Exception;

/**
 * The npm executable could not be found.
 */
final class NpmNotFoundException extends Exception
{
    /**
     * Construct a new npm not found exception.
     *
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct(Exception $cause = null)
    {
        parent::__construct(
            'The npm executable could not be found.',
            0,
            $cause
        );
    }
}
