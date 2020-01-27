<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';
$loader = require_once 'vendor/autoload.php';

\VCR\VCR::configure()->setBlackList([
    'phpunit/phpunit/Util/Filesystem.php',
]);
\VCR\VCR::turnOn();
\VCR\VCR::turnOff();
