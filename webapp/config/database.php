<?php
/**
 * Database Configuration
 * 
 * Update these values to match your MySQL/MariaDB setup
 */

return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'rankmath_webapp',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => 'rm_',
    
    // Connection options
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
