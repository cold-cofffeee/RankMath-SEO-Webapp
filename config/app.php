<?php
/**
 * Application Configuration
 */

return [
    'name' => 'RankMath SEO Webapp',
    'version' => '1.0.0',
    'timezone' => 'UTC',
    'debug' => true, // Set to false in production
    
    // Base paths
    'base_path' => dirname(__DIR__),
    'base_url' => 'http://localhost/rankmath/webapp',
    
    // API Configuration
    'api' => [
        'version' => 'v1',
        'rate_limit' => 1000, // requests per hour
    ],
    
    // Session Configuration
    'session' => [
        'lifetime' => 7200, // 2 hours in seconds
        'cookie_name' => 'rankmath_session',
        'secure' => false, // Set to true if using HTTPS
        'httponly' => true,
    ],
    
    // Features enabled
    'features' => [
        'seo_analysis' => true,
        'competitor_analysis' => true,
        'analytics' => true,
        'content_ai' => true,
        'local_seo' => true,
        'image_seo' => true,
        'news_sitemap' => true,
        'video_sitemap' => true,
        '404_monitor' => true,
        'redirections' => true,
        'schema_markup' => true,
    ],
];
