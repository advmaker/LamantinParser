<?php
require_once 'vendor/autoload.php';
use Lamantin\Console\Application;
define('BASE_DIR', __DIR__);
mb_internal_encoding('UTF-8');

$application = new Application('Lamantin Application', '0.1.1');
$application->run();