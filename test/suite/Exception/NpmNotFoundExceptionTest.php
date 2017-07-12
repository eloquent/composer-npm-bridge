<?php

namespace Eloquent\Composer\NpmBridge\Exception;

use Exception;
use PHPUnit\Framework\TestCase;

class NpmNotFoundExceptionTest extends TestCase
{
    public function testException()
    {
        $cause = new Exception();
        $exception = new NpmNotFoundException($cause);

        $this->assertSame('The npm executable could not be found.', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($cause, $exception->getPrevious());
    }
}
