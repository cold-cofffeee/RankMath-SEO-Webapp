<?php
/**
 * API Entry Point
 * Routes all API requests
 */

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Response.php';

// Load controllers
require_once __DIR__ . '/modules/SeoAnalysis/SeoAnalysisController.php';
require_once __DIR__ . '/modules/Analytics/AnalyticsController.php';
require_once __DIR__ . '/modules/Monitor/MonitorController.php';
require_once __DIR__ . '/modules/Redirections/RedirectionsController.php';
require_once __DIR__ . '/modules/LocalSeo/LocalSeoController.php';
require_once __DIR__ . '/modules/ImageSeo/ImageSeoController.php';
require_once __DIR__ . '/modules/ContentAi/ContentAiController.php';
require_once __DIR__ . '/modules/Sitemap/SitemapController.php';

use RankMathWebapp\Core\Router;
use RankMathWebapp\Core\Response;

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Initialize router
$router = new Router();

// Add error handling middleware
$router->addMiddleware(function() {
    try {
        return true;
    } catch (Exception $e) {
        Response::serverError($e->getMessage());
        return false;
    }
});

// ============================================
// SEO ANALYSIS ROUTES
// ============================================
$router->post('/api/seo-analysis/analyze', 'RankMathWebapp\\Modules\\SeoAnalysis\\SeoAnalysisController@analyze');
$router->get('/api/seo-analysis/history', 'RankMathWebapp\\Modules\\SeoAnalysis\\SeoAnalysisController@getHistory');

// ============================================
// ANALYTICS ROUTES
// ============================================
$router->get('/api/analytics/dashboard', 'RankMathWebapp\\Modules\\Analytics\\AnalyticsController@getDashboard');
$router->post('/api/analytics/keyword', 'RankMathWebapp\\Modules\\Analytics\\AnalyticsController@addKeyword');
$router->post('/api/analytics/import-gsc', 'RankMathWebapp\\Modules\\Analytics\\AnalyticsController@importGSC');

// ============================================
// 404 MONITOR ROUTES
// ============================================
$router->get('/api/404-monitor/logs', 'RankMathWebapp\\Modules\\Monitor\\MonitorController@getLogs');
$router->post('/api/404-monitor/log', 'RankMathWebapp\\Modules\\Monitor\\MonitorController@log404');
$router->delete('/api/404-monitor/{id}', 'RankMathWebapp\\Modules\\Monitor\\MonitorController@deleteLog');
$router->post('/api/404-monitor/clear', 'RankMathWebapp\\Modules\\Monitor\\MonitorController@clearLogs');
$router->get('/api/404-monitor/export', 'RankMathWebapp\\Modules\\Monitor\\MonitorController@exportCSV');

// ============================================
// REDIRECTIONS ROUTES
// ============================================
$router->get('/api/redirections', 'RankMathWebapp\\Modules\\Redirections\\RedirectionsController@getAll');
$router->post('/api/redirections', 'RankMathWebapp\\Modules\\Redirections\\RedirectionsController@add');
$router->put('/api/redirections/{id}', 'RankMathWebapp\\Modules\\Redirections\\RedirectionsController@update');
$router->delete('/api/redirections/{id}', 'RankMathWebapp\\Modules\\Redirections\\RedirectionsController@delete');
$router->get('/api/redirections/check', 'RankMathWebapp\\Modules\\Redirections\\RedirectionsController@checkRedirect');
$router->post('/api/redirections/import', 'RankMathWebapp\\Modules\\Redirections\\RedirectionsController@importCSV');

// ============================================
// LOCAL SEO ROUTES
// ============================================
$router->get('/api/local-seo/locations', 'RankMathWebapp\\Modules\\LocalSeo\\LocalSeoController@getLocations');
$router->get('/api/local-seo/locations/{id}', 'RankMathWebapp\\Modules\\LocalSeo\\LocalSeoController@getLocation');
$router->post('/api/local-seo/locations', 'RankMathWebapp\\Modules\\LocalSeo\\LocalSeoController@addLocation');
$router->put('/api/local-seo/locations/{id}', 'RankMathWebapp\\Modules\\LocalSeo\\LocalSeoController@updateLocation');
$router->delete('/api/local-seo/locations/{id}', 'RankMathWebapp\\Modules\\LocalSeo\\LocalSeoController@deleteLocation');
$router->get('/api/local-seo/locations/{id}/schema', 'RankMathWebapp\\Modules\\LocalSeo\\LocalSeoController@getSchema');
$router->get('/api/local-seo/nearby', 'RankMathWebapp\\Modules\\LocalSeo\\LocalSeoController@searchNearby');

// ============================================
// IMAGE SEO ROUTES
// ============================================
$router->get('/api/image-seo/images', 'RankMathWebapp\\Modules\\ImageSeo\\ImageSeoController@getImages');
$router->post('/api/image-seo/analyze', 'RankMathWebapp\\Modules\\ImageSeo\\ImageSeoController@analyzeImage');
$router->put('/api/image-seo/{id}', 'RankMathWebapp\\Modules\\ImageSeo\\ImageSeoController@updateImage');
$router->post('/api/image-seo/bulk-analyze', 'RankMathWebapp\\Modules\\ImageSeo\\ImageSeoController@bulkAnalyze');
$router->get('/api/image-seo/suggest-alt', 'RankMathWebapp\\Modules\\ImageSeo\\ImageSeoController@suggestAltText');
$router->get('/api/image-seo/{id}/tips', 'RankMathWebapp\\Modules\\ImageSeo\\ImageSeoController@getOptimizationTips');

// ============================================
// CONTENT AI ROUTES
// ============================================
$router->post('/api/content-ai/generate', 'RankMathWebapp\\Modules\\ContentAi\\ContentAiController@generateContent');
$router->get('/api/content-ai/suggestions', 'RankMathWebapp\\Modules\\ContentAi\\ContentAiController@getSuggestions');
$router->get('/api/content-ai/history', 'RankMathWebapp\\Modules\\ContentAi\\ContentAiController@getHistory');
$router->post('/api/content-ai/rewrite', 'RankMathWebapp\\Modules\\ContentAi\\ContentAiController@rewriteContent');
$router->get('/api/content-ai/research', 'RankMathWebapp\\Modules\\ContentAi\\ContentAiController@researchKeyword');

// ============================================
// SITEMAP ROUTES
// ============================================
$router->get('/api/sitemap', 'RankMathWebapp\\Modules\\Sitemap\\SitemapController@getAll');
$router->post('/api/sitemap', 'RankMathWebapp\\Modules\\Sitemap\\SitemapController@add');
$router->delete('/api/sitemap/{id}', 'RankMathWebapp\\Modules\\Sitemap\\SitemapController@delete');
$router->get('/api/sitemap/generate-xml', 'RankMathWebapp\\Modules\\Sitemap\\SitemapController@generateXML');
$router->post('/api/sitemap/crawl', 'RankMathWebapp\\Modules\\Sitemap\\SitemapController@crawlAndGenerate');

// ============================================
// HEALTH CHECK
// ============================================
$router->get('/api/health', function() {
    Response::success('API is running', [
        'version' => '1.0.0',
        'timestamp' => time(),
    ]);
});

// Dispatch the request
$router->dispatch();
