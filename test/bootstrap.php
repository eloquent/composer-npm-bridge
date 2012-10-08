<?php

use Typhoon\Typhoon;

require __DIR__.'/../vendor/autoload.php';

Eloquent\Asplode\Asplode::instance()->install();
Phake::setClient(Phake::CLIENT_PHPUNIT);
