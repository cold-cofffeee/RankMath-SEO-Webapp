<?php
/**
 * SEO Analysis Controller
 * Analyzes website SEO factors and competitor sites
 */

namespace RankMathWebapp\Modules\SeoAnalysis;

use RankMathWebapp\Core\Database;
use RankMathWebapp\Core\Response;

class SeoAnalysisController {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Analyze a URL for SEO factors
     */
    public function analyze($params) {
        // Read JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        $url = $input['url'] ?? '';
        $projectId = $input['project_id'] ?? null;
        $isCompetitor = $input['is_competitor'] ?? false;
        
        if (empty($url)) {
            Response::error('URL is required');
        }
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Response::error('Invalid URL format');
        }
        
        // Perform analysis
        $results = $this->performAnalysis($url);
        
        // Calculate score
        $score = $this->calculateScore($results);
        
        // Store results
        $analysisId = $this->db->insert('seo_analysis', [
            'project_id' => $projectId,
            'url' => $url,
            'analysis_type' => $isCompetitor ? 'competitor' : 'site',
            'score' => $score,
            'results' => json_encode($results),
            'analyzed_at' => date('Y-m-d H:i:s')
        ]);
        
        Response::success('Analysis complete', [
            'id' => $analysisId,
            'url' => $url,
            'score' => $score,
            'results' => $results
        ]);
    }
    
    /**
     * Get analysis history
     */
    public function getHistory($params) {
        $projectId = $_GET['project_id'] ?? null;
        $analysisType = $_GET['type'] ?? 'site';
        $limit = $_GET['limit'] ?? 10;
        
        $prefix = $this->db->getPrefix();
        $sql = "SELECT * FROM {$prefix}seo_analysis 
                WHERE analysis_type = :type";
        
        $queryParams = ['type' => $analysisType];
        
        if ($projectId) {
            $sql .= " AND project_id = :project_id";
            $queryParams['project_id'] = $projectId;
        }
        
        $sql .= " ORDER BY analyzed_at DESC LIMIT :limit";
        $queryParams['limit'] = (int)$limit;
        
        $results = $this->db->fetchAll($sql, $queryParams);
        
        // Parse JSON results
        foreach ($results as &$result) {
            $result['results'] = json_decode($result['results'], true);
        }
        
        Response::success('Analysis history retrieved', $results);
    }
    
    /**
     * Perform SEO analysis on a URL
     */
    private function performAnalysis($url) {
        $results = [
            'basic' => $this->analyzeBasics($url),
            'meta' => $this->analyzeMeta($url),
            'headings' => $this->analyzeHeadings($url),
            'images' => $this->analyzeImages($url),
            'links' => $this->analyzeLinks($url),
            'performance' => $this->analyzePerformance($url),
            'mobile' => $this->analyzeMobile($url),
            'security' => $this->analyzeSecurity($url),
            'structured_data' => $this->analyzeStructuredData($url),
        ];
        
        return $results;
    }
    
    private function analyzeBasics($url) {
        $html = $this->fetchUrl($url);
        if (!$html) {
            return ['error' => 'Failed to fetch URL'];
        }
        
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        
        // Title
        $titleNodes = $dom->getElementsByTagName('title');
        $title = $titleNodes->length > 0 ? $titleNodes->item(0)->textContent : '';
        $titleLength = mb_strlen($title);
        
        return [
            'title' => $title,
            'title_length' => $titleLength,
            'title_optimal' => $titleLength >= 30 && $titleLength <= 60,
            'html_size' => strlen($html),
            'word_count' => str_word_count(strip_tags($html)),
        ];
    }
    
    private function analyzeMeta($url) {
        $html = $this->fetchUrl($url);
        if (!$html) {
            return ['error' => 'Failed to fetch URL'];
        }
        
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        $metaTags = $xpath->query('//meta');
        $meta = [];
        
        foreach ($metaTags as $tag) {
            $name = $tag->getAttribute('name') ?: $tag->getAttribute('property');
            $content = $tag->getAttribute('content');
            
            if ($name) {
                $meta[$name] = $content;
            }
        }
        
        $description = $meta['description'] ?? '';
        $descLength = mb_strlen($description);
        
        return [
            'description' => $description,
            'description_length' => $descLength,
            'description_optimal' => $descLength >= 120 && $descLength <= 160,
            'keywords' => $meta['keywords'] ?? '',
            'robots' => $meta['robots'] ?? '',
            'og_title' => $meta['og:title'] ?? '',
            'og_description' => $meta['og:description'] ?? '',
            'og_image' => $meta['og:image'] ?? '',
            'twitter_card' => $meta['twitter:card'] ?? '',
        ];
    }
    
    private function analyzeHeadings($url) {
        $html = $this->fetchUrl($url);
        if (!$html) {
            return ['error' => 'Failed to fetch URL'];
        }
        
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        
        $headings = [];
        for ($i = 1; $i <= 6; $i++) {
            $tags = $dom->getElementsByTagName("h$i");
            $headings["h$i"] = [];
            foreach ($tags as $tag) {
                $headings["h$i"][] = trim($tag->textContent);
            }
        }
        
        return [
            'headings' => $headings,
            'h1_count' => count($headings['h1']),
            'h1_optimal' => count($headings['h1']) === 1,
            'hierarchy_proper' => !empty($headings['h1']),
        ];
    }
    
    private function analyzeImages($url) {
        $html = $this->fetchUrl($url);
        if (!$html) {
            return ['error' => 'Failed to fetch URL'];
        }
        
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        
        $images = $dom->getElementsByTagName('img');
        $totalImages = $images->length;
        $imagesWithAlt = 0;
        $imagesList = [];
        
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            $alt = $img->getAttribute('alt');
            
            if (!empty($alt)) {
                $imagesWithAlt++;
            }
            
            $imagesList[] = [
                'src' => $src,
                'alt' => $alt,
                'has_alt' => !empty($alt),
            ];
        }
        
        return [
            'total_images' => $totalImages,
            'images_with_alt' => $imagesWithAlt,
            'images_without_alt' => $totalImages - $imagesWithAlt,
            'alt_ratio' => $totalImages > 0 ? round(($imagesWithAlt / $totalImages) * 100, 2) : 0,
            'images' => $imagesList,
        ];
    }
    
    private function analyzeLinks($url) {
        $html = $this->fetchUrl($url);
        if (!$html) {
            return ['error' => 'Failed to fetch URL'];
        }
        
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        
        $links = $dom->getElementsByTagName('a');
        $internalLinks = 0;
        $externalLinks = 0;
        $nofollowLinks = 0;
        
        $parsedUrl = parse_url($url);
        $baseDomain = $parsedUrl['host'];
        
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $rel = $link->getAttribute('rel');
            
            if (empty($href) || $href === '#') {
                continue;
            }
            
            if (strpos($rel, 'nofollow') !== false) {
                $nofollowLinks++;
            }
            
            $linkHost = parse_url($href, PHP_URL_HOST);
            if ($linkHost === null || $linkHost === $baseDomain) {
                $internalLinks++;
            } else {
                $externalLinks++;
            }
        }
        
        return [
            'total_links' => $links->length,
            'internal_links' => $internalLinks,
            'external_links' => $externalLinks,
            'nofollow_links' => $nofollowLinks,
        ];
    }
    
    private function analyzePerformance($url) {
        $start = microtime(true);
        $html = $this->fetchUrl($url);
        $loadTime = microtime(true) - $start;
        
        return [
            'load_time' => round($loadTime, 3),
            'load_time_optimal' => $loadTime < 3,
            'page_size' => strlen($html),
        ];
    }
    
    private function analyzeMobile($url) {
        $html = $this->fetchUrl($url);
        if (!$html) {
            return ['error' => 'Failed to fetch URL'];
        }
        
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        $viewport = $xpath->query('//meta[@name="viewport"]');
        $hasViewport = $viewport->length > 0;
        
        return [
            'has_viewport' => $hasViewport,
            'viewport_content' => $hasViewport ? $viewport->item(0)->getAttribute('content') : '',
            'mobile_friendly' => $hasViewport,
        ];
    }
    
    private function analyzeSecurity($url) {
        $isHttps = strpos($url, 'https://') === 0;
        
        return [
            'uses_https' => $isHttps,
            'secure' => $isHttps,
        ];
    }
    
    private function analyzeStructuredData($url) {
        $html = $this->fetchUrl($url);
        if (!$html) {
            return ['error' => 'Failed to fetch URL'];
        }
        
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        
        $jsonLd = $xpath->query('//script[@type="application/ld+json"]');
        $schemas = [];
        
        foreach ($jsonLd as $script) {
            $data = json_decode($script->textContent, true);
            if ($data) {
                $schemas[] = $data;
            }
        }
        
        return [
            'has_schema' => count($schemas) > 0,
            'schema_count' => count($schemas),
            'schemas' => $schemas,
        ];
    }
    
    private function calculateScore($results) {
        $score = 0;
        $maxScore = 100;
        
        // Title (10 points)
        if (!empty($results['basic']['title'])) {
            $score += 5;
            if ($results['basic']['title_optimal']) {
                $score += 5;
            }
        }
        
        // Meta Description (10 points)
        if (!empty($results['meta']['description'])) {
            $score += 5;
            if ($results['meta']['description_optimal']) {
                $score += 5;
            }
        }
        
        // Headings (10 points)
        if ($results['headings']['h1_optimal']) {
            $score += 10;
        }
        
        // Images Alt Text (15 points)
        if ($results['images']['alt_ratio'] >= 90) {
            $score += 15;
        } else if ($results['images']['alt_ratio'] >= 50) {
            $score += 10;
        } else if ($results['images']['alt_ratio'] > 0) {
            $score += 5;
        }
        
        // Performance (15 points)
        if ($results['performance']['load_time_optimal']) {
            $score += 15;
        } else if ($results['performance']['load_time'] < 5) {
            $score += 10;
        } else {
            $score += 5;
        }
        
        // Mobile Friendly (10 points)
        if ($results['mobile']['mobile_friendly']) {
            $score += 10;
        }
        
        // HTTPS (10 points)
        if ($results['security']['uses_https']) {
            $score += 10;
        }
        
        // Structured Data (10 points)
        if ($results['structured_data']['has_schema']) {
            $score += 10;
        }
        
        // Links (10 points)
        if ($results['links']['internal_links'] > 5) {
            $score += 5;
        }
        if ($results['links']['external_links'] > 0) {
            $score += 5;
        }
        
        // Content (10 points)
        if ($results['basic']['word_count'] >= 300) {
            $score += 5;
        }
        if ($results['basic']['word_count'] >= 1000) {
            $score += 5;
        }
        
        return min($score, $maxScore);
    }
    
    private function fetchUrl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'RankMath SEO Webapp/1.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $html = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("cURL Error: $error");
            return false;
        }
        
        return $html;
    }
}
