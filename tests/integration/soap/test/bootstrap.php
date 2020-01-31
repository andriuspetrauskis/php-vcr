<?php
require_once __DIR__ . '/../../../../vendor/autoload.php';
$loader = require_once 'vendor/autoload.php';

\VCR\VCR::configure()->setWhiteList([
    'VCR/Example/',
]);
\VCR\VCR::turnOn();
\VCR\VCR::turnOff();
