<?php
/**
 * Auto-detect Base Path Configuration
 * This file automatically detects where the app is installed
 * Works on localhost, subdirectories, and live servers!
 */

// Detect the base path automatically
function getBasePath() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $baseDir = dirname($scriptName);
    
    // Normalize the path
    $baseDir = str_replace('\\', '/', $baseDir);
    
    // If we're in the root, return just /
    if ($baseDir === '/' || $baseDir === '') {
        return '';
    }
    
    return $baseDir;
}

// Detect the base URL automatically
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = getBasePath();
    
    return $protocol . '://' . $host . $basePath;
}

// Get API base path
function getApiBase() {
    return getBasePath() . '/api.php';
}

// Export for JavaScript
function getConfigForJS() {
    return [
        'basePath' => getBasePath(),
        'baseUrl' => getBaseUrl(),
        'apiBase' => getApiBase(),
    ];
}

// Define constants
define('BASE_PATH', getBasePath());
define('BASE_URL', getBaseUrl());
define('API_BASE', getApiBase());
