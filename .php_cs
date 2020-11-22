<?php

$config = Eloquent\CodeStyle\Config::create(__DIR__);
$config->setCacheFile(__DIR__ . '/artifacts/lint/php-cs-fixer/cache');

return $config;
