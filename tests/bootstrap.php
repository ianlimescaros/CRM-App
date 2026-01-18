<?php
// Testing bootstrap: set testing env and autoload
putenv('TESTING=1');
require_once __DIR__ . '/../vendor/autoload.php';
// Ensure error reporting is verbose for CI
error_reporting(E_ALL);
ini_set('display_errors', '1');
