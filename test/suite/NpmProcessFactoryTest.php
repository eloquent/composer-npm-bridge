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

use Phake;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Process\ExecutableFinder;

class NpmProcessFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->executableFinder = Phake::mock('Symfony\Component\Process\ExecutableFinder');
        $this->factory = new NpmProcessFactory($this->executableFinder);

        Phake::when($this->executableFinder)->find('npm')->thenReturn('/path/to/npm');
    }

    public function testConstructor()
    {
        $this->assertSame($this->executableFinder, $this->factory->executableFinder());
    }

    public function testConstructorDefaults()
    {
        $this->factory = new NpmProcessFactory;

        $this->assertEquals(new ExecutableFinder, $this->factory->executableFinder());
    }

    public function testCreate()
    {
        $processA = $this->factory->create(array('argumentA', 'argumentB'));
        $processB = $this->factory->create(array('argumentC', 'argumentD'));

        $this->assertInstanceOf('Symfony\Component\Process\Process', $processA);
        $this->assertInstanceOf('Symfony\Component\Process\Process', $processB);
        $this->assertSame("'/path/to/npm' 'argumentA' 'argumentB'", $processA->getCommandLine());
        $this->assertSame("'/path/to/npm' 'argumentC' 'argumentD'", $processB->getCommandLine());
    }

    public function testCreateFailure()
    {
        Phake::when($this->executableFinder)->find('npm')->thenReturn(null);

        $this->setExpectedException('Eloquent\Composer\NpmBridge\Exception\NpmNotFoundException');
        $this->factory->create(array());
    }
}
