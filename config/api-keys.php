<?php
/**
 * API Keys Configuration
 * SECURITY: Never commit this file to Git!
 * Add to .gitignore: config/api-keys.php
 */

return [
    // Google Gemini API Configuration
    'gemini' => [
        'api_key' => 'AIzaSyAPKvJr5Vgio2vxTV6GSQS2eB0gxVGJnsk', // Add your Gemini API key here
        'model' => 'gemini-2.0-flash-exp',
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
    ],
    
    // Google Search Console (for keyword research)
    'google_search_console' => [
        'client_id' => '',
        'client_secret' => '',
        'redirect_uri' => '',
    ],
    
    // Optional: SEMrush for keyword difficulty
    'semrush' => [
        'api_key' => '',
    ],
];
