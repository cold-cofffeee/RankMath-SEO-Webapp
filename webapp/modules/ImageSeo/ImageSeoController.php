<?php
/**
 * Image SEO Controller
 * Optimize and manage image SEO
 */

namespace RankMathWebapp\Modules\ImageSeo;

use RankMathWebapp\Core\Database;
use RankMathWebapp\Core\Response;

class ImageSeoController {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all images
     */
    public function getImages($params) {
        $projectId = $_GET['project_id'] ?? null;
        $optimized = $_GET['optimized'] ?? null;
        
        $prefix = $this->db->getPrefix();
        $sql = "SELECT * FROM {$prefix}image_seo WHERE 1=1";
        $queryParams = [];
        
        if ($projectId) {
            $sql .= " AND project_id = :project_id";
            $queryParams['project_id'] = $projectId;
        }
        
        if ($optimized !== null) {
            $sql .= " AND optimized = :optimized";
            $queryParams['optimized'] = (int)$optimized;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $images = $this->db->fetchAll($sql, $queryParams);
        
        Response::success('Images retrieved', $images);
    }
    
    /**
     * Analyze image SEO
     */
    public function analyzeImage($params) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $imageUrl = $data['image_url'] ?? '';
        
        if (empty($imageUrl)) {
            Response::error('Image URL is required');
        }
        
        $analysis = $this->performImageAnalysis($imageUrl);
        
        // Store in database
        $imageId = $this->db->insert('image_seo', [
            'project_id' => $data['project_id'] ?? null,
            'image_url' => $imageUrl,
            'alt_text' => $analysis['alt_text'],
            'title' => $analysis['title'],
            'caption' => $analysis['caption'],
            'description' => $analysis['description'],
            'file_size' => $analysis['file_size'],
            'dimensions' => $analysis['dimensions'],
            'optimized' => $analysis['is_optimized'] ? 1 : 0,
        ]);
        
        Response::success('Image analyzed', array_merge(['id' => $imageId], $analysis));
    }
    
    /**
     * Update image SEO data
     */
    public function updateImage($params) {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Image ID is required');
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $updateData = [];
        $allowedFields = ['alt_text', 'title', 'caption', 'description', 'optimized'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            Response::error('No data to update');
        }
        
        $this->db->update('image_seo', $updateData, 'id = :id', ['id' => $id]);
        
        Response::success('Image updated');
    }
    
    /**
     * Bulk optimize images from a URL
     */
    public function bulkAnalyze($params) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $url = $data['url'] ?? '';
        $projectId = $data['project_id'] ?? null;
        
        if (empty($url)) {
            Response::error('URL is required');
        }
        
        // Fetch HTML
        $html = $this->fetchUrl($url);
        if (!$html) {
            Response::error('Failed to fetch URL');
        }
        
        // Extract images
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $images = $dom->getElementsByTagName('img');
        
        $analyzed = [];
        $baseUrl = $this->getBaseUrl($url);
        
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            $alt = $img->getAttribute('alt');
            $title = $img->getAttribute('title');
            
            // Convert relative URLs to absolute
            if (strpos($src, 'http') !== 0) {
                $src = $baseUrl . ltrim($src, '/');
            }
            
            // Get image info
            $imageInfo = $this->getImageInfo($src);
            
            $imageId = $this->db->insert('image_seo', [
                'project_id' => $projectId,
                'image_url' => $src,
                'alt_text' => $alt,
                'title' => $title,
                'file_size' => $imageInfo['size'] ?? null,
                'dimensions' => $imageInfo['dimensions'] ?? null,
                'optimized' => !empty($alt) && $imageInfo['size'] < 200000 ? 1 : 0,
            ]);
            
            $analyzed[] = [
                'id' => $imageId,
                'src' => $src,
                'alt' => $alt,
                'title' => $title,
                'size' => $imageInfo['size'] ?? 0,
                'dimensions' => $imageInfo['dimensions'] ?? '',
                'has_alt' => !empty($alt),
                'optimized' => !empty($alt) && ($imageInfo['size'] ?? 0) < 200000,
            ];
        }
        
        Response::success("Analyzed {$images->length} images", $analyzed);
    }
    
    /**
     * Generate alt text suggestions using AI
     */
    public function suggestAltText($params) {
        $imageUrl = $_GET['image_url'] ?? '';
        
        if (empty($imageUrl)) {
            Response::error('Image URL is required');
        }
        
        // Extract filename and create basic suggestion
        $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        
        // Convert filename to readable text
        $suggestion = str_replace(['-', '_'], ' ', $filename);
        $suggestion = ucwords($suggestion);
        
        Response::success('Alt text suggestion generated', [
            'suggestion' => $suggestion,
            'tips' => [
                'Be descriptive and specific',
                'Include relevant keywords naturally',
                'Keep it under 125 characters',
                'Avoid "image of" or "picture of"',
            ]
        ]);
    }
    
    /**
     * Get image optimization tips
     */
    public function getOptimizationTips($params) {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Image ID is required');
        }
        
        $prefix = $this->db->getPrefix();
        $image = $this->db->fetchOne(
            "SELECT * FROM {$prefix}image_seo WHERE id = :id",
            ['id' => $id]
        );
        
        if (!$image) {
            Response::notFound('Image not found');
        }
        
        $tips = [];
        
        if (empty($image['alt_text'])) {
            $tips[] = [
                'type' => 'error',
                'message' => 'Missing alt text - Add descriptive alt text for accessibility and SEO'
            ];
        }
        
        if (empty($image['title'])) {
            $tips[] = [
                'type' => 'warning',
                'message' => 'Missing title attribute - Consider adding a title for better UX'
            ];
        }
        
        if ($image['file_size'] > 200000) {
            $tips[] = [
                'type' => 'error',
                'message' => 'Large file size - Compress image to under 200KB for faster loading'
            ];
        } else if ($image['file_size'] > 100000) {
            $tips[] = [
                'type' => 'warning',
                'message' => 'File size could be optimized - Consider compressing to under 100KB'
            ];
        }
        
        if (empty($tips)) {
            $tips[] = [
                'type' => 'success',
                'message' => 'Image is well optimized!'
            ];
        }
        
        Response::success('Optimization tips generated', $tips);
    }
    
    private function performImageAnalysis($imageUrl) {
        $imageInfo = $this->getImageInfo($imageUrl);
        
        return [
            'alt_text' => '',
            'title' => '',
            'caption' => '',
            'description' => '',
            'file_size' => $imageInfo['size'] ?? 0,
            'dimensions' => $imageInfo['dimensions'] ?? '',
            'is_optimized' => ($imageInfo['size'] ?? 999999) < 200000,
            'recommendations' => $this->getRecommendations($imageInfo),
        ];
    }
    
    private function getImageInfo($imageUrl) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        
        // Try to get dimensions
        $dimensions = '';
        try {
            $imageData = @getimagesize($imageUrl);
            if ($imageData) {
                $dimensions = $imageData[0] . 'x' . $imageData[1];
            }
        } catch (\Exception $e) {
            // Ignore errors
        }
        
        return [
            'size' => $size > 0 ? $size : null,
            'dimensions' => $dimensions,
        ];
    }
    
    private function getRecommendations($imageInfo) {
        $recommendations = [];
        
        if (($imageInfo['size'] ?? 0) > 200000) {
            $recommendations[] = 'Compress image to reduce file size below 200KB';
        }
        
        $recommendations[] = 'Add descriptive alt text';
        $recommendations[] = 'Use descriptive filename';
        $recommendations[] = 'Consider using WebP format for better compression';
        
        return $recommendations;
    }
    
    private function fetchUrl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $html = curl_exec($ch);
        curl_close($ch);
        
        return $html;
    }
    
    private function getBaseUrl($url) {
        $parts = parse_url($url);
        return $parts['scheme'] . '://' . $parts['host'];
    }
}
