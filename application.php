#!/usr/bin/env php
<?php
require_once 'vendor/autoload.php';
use Lamantin\Console\Application;

$application = new Application('Lamantin Application', '0.1.1');
$application->run();