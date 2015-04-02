<?php
/**
 * Display all errors when APPLICATION_ENV is development.
 */
if ($_SERVER['APPLICATION_ENV']
        == 'development') {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

/**
 * Make sure timezone for all operations is set to Luxembourg
 */
date_default_timezone_set('Europe/Luxembourg');

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
define('PROJECT_PATH',
        realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'));
chdir(PROJECT_PATH);

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/certificat.config.php')->run();
