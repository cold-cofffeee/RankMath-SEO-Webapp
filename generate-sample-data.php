<?php
/**
 * Generate Sample Data for Demo
 * Run this file once to populate the database with sample data
 */

require_once __DIR__ . '/core/Database.php';

use RankMathWebapp\Core\Database;

$db = Database::getInstance();
$prefix = $db->getPrefix();

echo "Generating sample data...\n\n";

// Check if data already exists
$existingKeywords = $db->fetchOne("SELECT COUNT(*) as count FROM {$prefix}analytics_keywords", []);
if ($existingKeywords['count'] > 0) {
    echo "Sample data already exists. Skipping...\n";
    exit;
}

// 1. Add sample analytics keywords (30 days of data)
echo "Adding sample keyword data...\n";
$keywords = [
    ['keyword' => 'seo optimization', 'base_impressions' => 5000, 'base_clicks' => 350, 'position' => 3.5],
    ['keyword' => 'digital marketing', 'base_impressions' => 8000, 'base_clicks' => 640, 'position' => 2.8],
    ['keyword' => 'content marketing', 'base_impressions' => 4500, 'base_clicks' => 270, 'position' => 4.2],
    ['keyword' => 'keyword research', 'base_impressions' => 3200, 'base_clicks' => 192, 'position' => 5.1],
    ['keyword' => 'link building', 'base_impressions' => 2800, 'base_clicks' => 140, 'position' => 6.3],
    ['keyword' => 'on page seo', 'base_impressions' => 4200, 'base_clicks' => 336, 'position' => 3.9],
    ['keyword' => 'technical seo', 'base_impressions' => 3600, 'base_clicks' => 252, 'position' => 4.5],
    ['keyword' => 'local seo', 'base_impressions' => 5500, 'base_clicks' => 440, 'position' => 3.2],
    ['keyword' => 'seo tools', 'base_impressions' => 6200, 'base_clicks' => 496, 'position' => 2.9],
    ['keyword' => 'google analytics', 'base_impressions' => 7800, 'base_clicks' => 546, 'position' => 2.5],
];

$keywordCount = 0;
for ($i = 30; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    
    foreach ($keywords as $kw) {
        // Add some variation to the numbers
        $variation = (90 + rand(0, 20)) / 100;
        $impressions = round($kw['base_impressions'] * $variation);
        $clicks = round($kw['base_clicks'] * $variation);
        $position = $kw['position'] + (rand(-10, 10) / 10);
        $ctr = $clicks > 0 ? round(($clicks / $impressions) * 100, 2) : 0;
        
        $db->insert('analytics_keywords', [
            'keyword' => $kw['keyword'],
            'impressions' => $impressions,
            'clicks' => $clicks,
            'position' => $position,
            'ctr' => $ctr,
            'date' => $date,
        ]);
        
        $keywordCount++;
    }
}
echo "Added $keywordCount keyword records.\n";

// 2. Add sample analytics pages
echo "Adding sample page data...\n";
$pages = [
    '/blog/seo-guide' => ['views' => 1500, 'visitors' => 1200],
    '/services/seo' => ['views' => 2300, 'visitors' => 1800],
    '/blog/content-marketing' => ['views' => 1800, 'visitors' => 1400],
    '/resources/tools' => ['views' => 3200, 'visitors' => 2500],
    '/about' => ['views' => 800, 'visitors' => 650],
];

$pageCount = 0;
for ($i = 30; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    
    foreach ($pages as $url => $stats) {
        $variation = (85 + rand(0, 30)) / 100;
        $pageviews = round($stats['views'] * $variation);
        $visitors = round($stats['visitors'] * $variation);
        
        $db->insert('analytics_pages', [
            'url' => $url,
            'pageviews' => $pageviews,
            'unique_visitors' => $visitors,
            'impressions' => $pageviews * rand(3, 8),
            'clicks' => round($pageviews * 0.15),
            'position' => rand(20, 50) / 10,
            'date' => $date,
        ]);
        
        $pageCount++;
    }
}
echo "Added $pageCount page records.\n";

// 3. Add sample SEO analyses
echo "Adding sample SEO analyses...\n";
$sampleAnalyses = [
    [
        'url' => 'https://example.com',
        'score' => 85,
        'type' => 'site'
    ],
    [
        'url' => 'https://mywebsite.com',
        'score' => 78,
        'type' => 'site'
    ],
    [
        'url' => 'https://competitor.com',
        'score' => 92,
        'type' => 'competitor'
    ],
];

$analysisCount = 0;
foreach ($sampleAnalyses as $analysis) {
    $results = json_encode([
        'basic' => [
            'title' => 'Sample Website - SEO Services',
            'title_length' => 35,
            'title_optimal' => true
        ],
        'meta' => [
            'description' => 'Professional SEO services to boost your website ranking',
            'description_length' => 55,
            'description_optimal' => false
        ],
        'headings' => [
            'h1_count' => 1,
            'h1_optimal' => true,
            'h2_count' => 5
        ],
        'images' => [
            'total_images' => 12,
            'images_with_alt' => 10,
            'alt_ratio' => 83
        ],
        'performance' => [
            'load_time' => 2.3,
            'load_time_optimal' => true
        ],
        'security' => [
            'uses_https' => true
        ],
        'mobile' => [
            'mobile_friendly' => true
        ],
        'structured_data' => [
            'has_schema' => true
        ]
    ]);
    
    $db->insert('seo_analysis', [
        'url' => $analysis['url'],
        'analysis_type' => $analysis['type'],
        'score' => $analysis['score'],
        'results' => $results,
    ]);
    
    $analysisCount++;
}
echo "Added $analysisCount SEO analysis records.\n";

// 4. Add sample 404 logs
echo "Adding sample 404 logs...\n";
$notFoundUrls = [
    '/old-page',
    '/deleted-post',
    '/moved-content',
    '/test-page',
];

$logCount = 0;
foreach ($notFoundUrls as $url) {
    $db->insert('404_monitor', [
        'uri' => $url,
        'referer' => 'https://google.com',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'ip_address' => '192.168.1.' . rand(1, 255),
        'hits' => rand(1, 25),
    ]);
    
    $logCount++;
}
echo "Added $logCount 404 log records.\n";

// 5. Add sample redirections
echo "Adding sample redirections...\n";
$redirects = [
    ['/old-blog' => '/blog', 'type' => '301', 'hits' => 45],
    ['/services-old' => '/services', 'type' => '301', 'hits' => 32],
    ['/temp-page' => '/new-page', 'type' => '302', 'hits' => 12],
];

$redirectCount = 0;
foreach ($redirects as $redirect) {
    foreach ($redirect as $source => $target) {
        if ($source === 'type' || $source === 'hits') continue;
        
        $db->insert('redirections', [
            'source_url' => $source,
            'target_url' => $target,
            'redirect_type' => $redirect['type'],
            'hits' => $redirect['hits'],
        ]);
        
        $redirectCount++;
    }
}
echo "Added $redirectCount redirection records.\n";

// 6. Add sample local SEO locations
echo "Adding sample local locations...\n";
$locations = [
    [
        'name' => 'Downtown Office',
        'address' => '123 Main Street',
        'city' => 'New York',
        'state' => 'NY',
        'country' => 'USA',
        'postal_code' => '10001',
        'phone' => '+1 (555) 123-4567',
        'latitude' => 40.7589,
        'longitude' => -73.9851
    ],
    [
        'name' => 'West Side Branch',
        'address' => '456 West Ave',
        'city' => 'Los Angeles',
        'state' => 'CA',
        'country' => 'USA',
        'postal_code' => '90001',
        'phone' => '+1 (555) 987-6543',
        'latitude' => 34.0522,
        'longitude' => -118.2437
    ],
];

$locationCount = 0;
foreach ($locations as $location) {
    $db->insert('local_locations', $location);
    $locationCount++;
}
echo "Added $locationCount location records.\n";

echo "\n✅ Sample data generation complete!\n";
echo "You can now view the dashboard with real data.\n";
