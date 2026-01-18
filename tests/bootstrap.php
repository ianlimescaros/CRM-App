<?php
// Testing bootstrap: set testing env and autoload
putenv('TESTING=1');
require_once __DIR__ . '/../vendor/autoload.php';
// Load lightweight app helpers used by unit tests
require_once __DIR__ . '/../src/services/Validator.php';
// Ensure error reporting is verbose for CI
error_reporting(E_ALL);
ini_set('display_errors', '1');
