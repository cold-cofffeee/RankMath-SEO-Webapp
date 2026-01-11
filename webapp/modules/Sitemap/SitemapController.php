<?php
/**
 * Sitemap Controller
 * Generate and manage XML sitemaps
 */

namespace RankMathWebapp\Modules\Sitemap;

use RankMathWebapp\Core\Database;
use RankMathWebapp\Core\Response;

class SitemapController {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all sitemap entries
     */
    public function getAll($params) {
        $projectId = $_GET['project_id'] ?? null;
        $type = $_GET['type'] ?? null;
        
        $prefix = $this->db->getPrefix();
        $sql = "SELECT * FROM {$prefix}sitemaps WHERE 1=1";
        $queryParams = [];
        
        if ($projectId) {
            $sql .= " AND project_id = :project_id";
            $queryParams['project_id'] = $projectId;
        }
        
        if ($type) {
            $sql .= " AND type = :type";
            $queryParams['type'] = $type;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $sitemaps = $this->db->fetchAll($sql, $queryParams);
        
        Response::success('Sitemaps retrieved', $sitemaps);
    }
    
    /**
     * Add sitemap entry
     */
    public function add($params) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $required = ['url'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::error("Field '$field' is required");
            }
        }
        
        $sitemapId = $this->db->insert('sitemaps', [
            'project_id' => $data['project_id'] ?? null,
            'type' => $data['type'] ?? 'general',
            'url' => $data['url'],
            'priority' => $data['priority'] ?? 0.5,
            'changefreq' => $data['changefreq'] ?? 'weekly',
            'last_modified' => $data['last_modified'] ?? date('Y-m-d H:i:s'),
        ]);
        
        Response::success('Sitemap entry added', ['id' => $sitemapId]);
    }
    
    /**
     * Delete sitemap entry
     */
    public function delete($params) {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Sitemap ID is required');
        }
        
        $this->db->delete('sitemaps', 'id = :id', ['id' => $id]);
        
        Response::success('Sitemap entry deleted');
    }
    
    /**
     * Generate XML sitemap
     */
    public function generateXML($params) {
        $projectId = $_GET['project_id'] ?? null;
        $type = $_GET['type'] ?? 'general';
        
        $prefix = $this->db->getPrefix();
        $sql = "SELECT * FROM {$prefix}sitemaps WHERE type = :type";
        $queryParams = ['type' => $type];
        
        if ($projectId) {
            $sql .= " AND project_id = :project_id";
            $queryParams['project_id'] = $projectId;
        }
        
        $entries = $this->db->fetchAll($sql, $queryParams);
        
        $xml = $this->buildXML($entries, $type);
        
        header('Content-Type: application/xml; charset=utf-8');
        echo $xml;
        exit;
    }
    
    /**
     * Crawl website and generate sitemap
     */
    public function crawlAndGenerate($params) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $baseUrl = $data['base_url'] ?? '';
        $projectId = $data['project_id'] ?? null;
        $maxPages = $data['max_pages'] ?? 100;
        
        if (empty($baseUrl)) {
            Response::error('Base URL is required');
        }
        
        $urls = $this->crawlWebsite($baseUrl, $maxPages);
        
        $added = 0;
        foreach ($urls as $url) {
            try {
                $this->db->insert('sitemaps', [
                    'project_id' => $projectId,
                    'type' => 'general',
                    'url' => $url,
                    'priority' => 0.5,
                    'changefreq' => 'weekly',
                    'last_modified' => date('Y-m-d H:i:s'),
                ]);
                $added++;
            } catch (\Exception $e) {
                // Skip duplicates
            }
        }
        
        Response::success("Crawled and added $added URLs", [
            'total_found' => count($urls),
            'added' => $added,
        ]);
    }
    
    private function buildXML($entries, $type) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        
        if ($type === 'news') {
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
            $xml .= 'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">' . "\n";
        } else if ($type === 'video') {
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
            $xml .= 'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";
        } else if ($type === 'image') {
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
            $xml .= 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
        } else {
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        }
        
        foreach ($entries as $entry) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($entry['url']) . "</loc>\n";
            
            if ($entry['last_modified']) {
                $xml .= "    <lastmod>" . date('Y-m-d', strtotime($entry['last_modified'])) . "</lastmod>\n";
            }
            
            $xml .= "    <changefreq>" . $entry['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $entry['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    private function crawlWebsite($baseUrl, $maxPages) {
        $visited = [];
        $toVisit = [$baseUrl];
        $urls = [];
        
        $baseDomain = parse_url($baseUrl, PHP_URL_HOST);
        
        while (count($toVisit) > 0 && count($urls) < $maxPages) {
            $currentUrl = array_shift($toVisit);
            
            if (in_array($currentUrl, $visited)) {
                continue;
            }
            
            $visited[] = $currentUrl;
            $urls[] = $currentUrl;
            
            // Fetch page
            $html = $this->fetchPage($currentUrl);
            if (!$html) {
                continue;
            }
            
            // Extract links
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $links = $dom->getElementsByTagName('a');
            
            foreach ($links as $link) {
                $href = $link->getAttribute('href');
                
                if (empty($href) || $href === '#') {
                    continue;
                }
                
                // Convert relative to absolute
                if (strpos($href, 'http') !== 0) {
                    $href = $this->makeAbsoluteUrl($baseUrl, $href);
                }
                
                // Only crawl same domain
                $linkDomain = parse_url($href, PHP_URL_HOST);
                if ($linkDomain === $baseDomain && !in_array($href, $visited) && !in_array($href, $toVisit)) {
                    $toVisit[] = $href;
                }
            }
        }
        
        return $urls;
    }
    
    private function fetchPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'RankMath Sitemap Crawler/1.0');
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200 ? $html : false;
    }
    
    private function makeAbsoluteUrl($base, $relative) {
        if (strpos($relative, 'http') === 0) {
            return $relative;
        }
        
        $parts = parse_url($base);
        $baseUrl = $parts['scheme'] . '://' . $parts['host'];
        
        if (strpos($relative, '/') === 0) {
            return $baseUrl . $relative;
        }
        
        return rtrim($baseUrl, '/') . '/' . ltrim($relative, '/');
    }
}
