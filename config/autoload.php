<?php

/**
 * Simple PHP Autoloader for Models and Controllers
 * Automatically loads classes from the 'models' and 'controllers' directories.
 */
spl_autoload_register(function ($class_name) {
    // Define the directories to search for classes
    $directories = [
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/'
    ];

    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';

        // If the file exists, require it and stop searching
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
