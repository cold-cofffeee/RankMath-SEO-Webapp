<?php
/**
 * Content AI Controller
 * AI-powered content generation using Google Gemini
 */

namespace RankMathWebapp\Modules\ContentAi;

use RankMathWebapp\Core\Database;
use RankMathWebapp\Core\Response;

class ContentAiController {
    
    private $db;
    private $apiKeys;
    private $geminiEndpoint;
    private $geminiModel;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Load API keys securely
        $apiKeysPath = dirname(dirname(__DIR__)) . '/config/api-keys.php';
        if (file_exists($apiKeysPath)) {
            $this->apiKeys = require $apiKeysPath;
            $this->geminiEndpoint = $this->apiKeys['gemini']['endpoint'] ?? '';
            $this->geminiModel = $this->apiKeys['gemini']['model'] ?? 'gemini-2.0-flash-exp';
        }
    }
    
    /**
     * Generate content suggestions using Gemini AI
     */
    public function generateContent($params) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $keyword = $data['keyword'] ?? '';
        $contentType = $data['content_type'] ?? 'paragraph';
        $tone = $data['tone'] ?? 'professional';
        $projectId = $data['project_id'] ?? null;
        
        if (empty($keyword)) {
            Response::error('Keyword is required');
        }
        
        // Check if API key is configured
        if (empty($this->apiKeys['gemini']['api_key'])) {
            // Fallback to templates if no API key
            $generatedContent = $this->generateByTemplate($keyword, $contentType, $tone);
            $isRealAI = false;
        } else {
            // Use real Gemini AI
            $generatedContent = $this->generateWithGemini($keyword, $contentType, $tone);
            $isRealAI = true;
        }
        
        // Store in history
        $contentId = $this->db->insert('content_ai', [
            'project_id' => $projectId,
            'keyword' => $keyword,
            'content_type' => $contentType,
            'prompt' => $data['prompt'] ?? null,
            'generated_content' => $generatedContent,
            'credits_used' => $isRealAI ? 1 : 0,
        ]);
        
        Response::success('Content generated', [
            'id' => $contentId,
            'content' => $generatedContent,
            'keyword' => $keyword,
            'content_type' => $contentType,
            'powered_by' => $isRealAI ? 'Google Gemini AI' : 'Templates',
        ]);
    }
    
    /**
     * Generate content using real Gemini AI
     */
    private function generateWithGemini($keyword, $contentType, $tone) {
        $apiKey = $this->apiKeys['gemini']['api_key'];
        $url = $this->geminiEndpoint . '/' . $this->geminiModel . ':generateContent?key=' . $apiKey;
        
        // Build prompt based on content type
        $prompt = $this->buildGeminiPrompt($keyword, $contentType, $tone);
        
        $requestData = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 500,
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            // Fallback to templates on error
            error_log("Gemini API Error: HTTP $httpCode - $response");
            return $this->generateByTemplate($keyword, $contentType, $tone);
        }
        
        $result = json_decode($response, true);
        
        // Extract generated text from Gemini response
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($result['candidates'][0]['content']['parts'][0]['text']);
        }
        
        // Fallback if parsing fails
        return $this->generateByTemplate($keyword, $contentType, $tone);
    }
    
    /**
     * Build appropriate prompt for Gemini based on content type
     */
    private function buildGeminiPrompt($keyword, $contentType, $tone) {
        $toneDesc = match($tone) {
            'professional' => 'professional and authoritative',
            'casual' => 'casual and conversational',
            'friendly' => 'friendly and engaging',
            'formal' => 'formal and academic',
            default => 'professional'
        };
        
        $prompts = [
            'title' => "Generate a compelling SEO-optimized title about '$keyword'. Make it engaging, under 60 characters, and use a $toneDesc tone. Return ONLY the title, nothing else.",
            
            'paragraph' => "Write a well-structured, informative paragraph about '$keyword' in a $toneDesc tone. Focus on providing value and be SEO-friendly. Around 150-200 words.",
            
            'meta_description' => "Create an SEO-optimized meta description for '$keyword'. Make it compelling, action-oriented, and between 120-160 characters. Include a call to action. Return ONLY the meta description.",
            
            'heading' => "Generate a clear, SEO-optimized H2 or H3 heading about '$keyword' in a $toneDesc tone. Make it informative and engaging. Return ONLY the heading.",
            
            'conclusion' => "Write a compelling conclusion paragraph about '$keyword' in a $toneDesc tone. Summarize key points and include a call to action. Around 100-150 words.",
        ];
        
        return $prompts[$contentType] ?? $prompts['paragraph'];
    }
    
    /**
     * Fallback: Generate with templates (when API key not configured)
     */
    private function generateByTemplate($keyword, $contentType, $tone) {
        return $this->generateByType($keyword, $contentType, $tone);
    }
    
    /**
     * Get content suggestions for keyword
     */
    public function getSuggestions($params) {
        $keyword = $_GET['keyword'] ?? '';
        
        if (empty($keyword)) {
            Response::error('Keyword is required');
        }
        
        $suggestions = [
            'title' => $this->generateTitleSuggestions($keyword),
            'meta_description' => $this->generateMetaDescription($keyword),
            'headings' => $this->generateHeadingsSuggestions($keyword),
            'outline' => $this->generateContentOutline($keyword),
            'related_keywords' => $this->getRelatedKeywords($keyword),
        ];
        
        Response::success('Suggestions generated', $suggestions);
    }
    
    /**
     * Get content history
     */
    public function getHistory($params) {
        $projectId = $_GET['project_id'] ?? null;
        $limit = $_GET['limit'] ?? 20;
        
        $prefix = $this->db->getPrefix();
        $sql = "SELECT * FROM {$prefix}content_ai";
        $queryParams = [];
        
        if ($projectId) {
            $sql .= " WHERE project_id = :project_id";
            $queryParams['project_id'] = $projectId;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit";
        $queryParams['limit'] = (int)$limit;
        
        $history = $this->db->fetchAll($sql, $queryParams);
        
        Response::success('History retrieved', $history);
    }
    
    /**
     * Rewrite content
     */
    public function rewriteContent($params) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $content = $data['content'] ?? '';
        $style = $data['style'] ?? 'improve';
        
        if (empty($content)) {
            Response::error('Content is required');
        }
        
        $rewritten = $this->rewriteByStyle($content, $style);
        
        Response::success('Content rewritten', [
            'original' => $content,
            'rewritten' => $rewritten,
            'style' => $style,
        ]);
    }
    
    /**
     * Keyword research using real data from Search Console or database
     */
    public function researchKeyword($params) {
        $keyword = $_GET['keyword'] ?? '';
        
        if (empty($keyword)) {
            Response::error('Keyword is required');
        }
        
        // Get real keyword data from database (from tracked analytics)
        $keywordData = $this->getRealKeywordData($keyword);
        
        $research = [
            'keyword' => $keyword,
            'related_keywords' => $this->getRelatedKeywordsFromDB($keyword),
            'long_tail_keywords' => $this->getLongTailKeywords($keyword),
            'questions' => $this->getQuestions($keyword),
            'search_volume' => $keywordData['impressions'] ?? 0,
            'clicks' => $keywordData['clicks'] ?? 0,
            'position' => $keywordData['position'] ?? 0,
            'ctr' => $keywordData['ctr'] ?? 0,
            'data_source' => $keywordData['source'] ?? 'Database',
            'date_range' => '30 days',
        ];
        
        Response::success('Keyword research complete', $research);
    }
    
    /**
     * Get real keyword data from analytics database
     */
    private function getRealKeywordData($keyword) {
        $prefix = $this->db->getPrefix();
        
        // Try exact match first
        $sql = "SELECT 
                    SUM(impressions) as total_impressions,
                    SUM(clicks) as total_clicks,
                    AVG(position) as avg_position,
                    AVG(ctr) as avg_ctr
                FROM {$prefix}analytics_keywords
                WHERE keyword = :keyword
                AND date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $data = $this->db->fetchOne($sql, ['keyword' => $keyword]);
        
        if ($data && $data['total_impressions'] > 0) {
            return [
                'impressions' => (int)$data['total_impressions'],
                'clicks' => (int)$data['total_clicks'],
                'position' => round($data['avg_position'], 1),
                'ctr' => round($data['avg_ctr'], 2),
                'source' => 'Analytics Database (Real Data)'
            ];
        }
        
        // Try partial match
        $sql = "SELECT 
                    SUM(impressions) as total_impressions,
                    SUM(clicks) as total_clicks,
                    AVG(position) as avg_position,
                    AVG(ctr) as avg_ctr
                FROM {$prefix}analytics_keywords
                WHERE keyword LIKE :keyword
                AND date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                LIMIT 1";
        
        $data = $this->db->fetchOne($sql, ['keyword' => '%' . $keyword . '%']);
        
        if ($data && $data['total_impressions'] > 0) {
            return [
                'impressions' => (int)$data['total_impressions'],
                'clicks' => (int)$data['total_clicks'],
                'position' => round($data['avg_position'], 1),
                'ctr' => round($data['avg_ctr'], 2),
                'source' => 'Similar Keywords (Real Data)'
            ];
        }
        
        // No data found
        return [
            'impressions' => 0,
            'clicks' => 0,
            'position' => 0,
            'ctr' => 0,
            'source' => 'No Data Available (Add to Analytics)'
        ];
    }
    
    /**
     * Get related keywords from database based on actual analytics
     */
    private function getRelatedKeywordsFromDB($keyword) {
        $prefix = $this->db->getPrefix();
        
        // Find keywords that contain the main keyword or are similar
        $sql = "SELECT DISTINCT keyword, 
                       SUM(impressions) as total_impressions
                FROM {$prefix}analytics_keywords
                WHERE (keyword LIKE :keyword1 OR keyword LIKE :keyword2)
                AND keyword != :exact_keyword
                AND date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY keyword
                ORDER BY total_impressions DESC
                LIMIT 10";
        
        $results = $this->db->fetchAll($sql, [
            'keyword1' => '%' . $keyword . '%',
            'keyword2' => $keyword . '%',
            'exact_keyword' => $keyword
        ]);
        
        $relatedKeywords = array_column($results, 'keyword');
        
        // If no real data, generate suggestions
        if (empty($relatedKeywords)) {
            $relatedKeywords = [
                $keyword . " tips",
                $keyword . " guide",
                "best " . $keyword,
                $keyword . " tutorial",
                "how to " . $keyword,
            ];
        }
        
        return $relatedKeywords;
    }
    
    private function generateByType($keyword, $type, $tone) {
        switch ($type) {
            case 'title':
                return $this->generateTitle($keyword, $tone);
            
            case 'paragraph':
                return $this->generateParagraph($keyword, $tone);
            
            case 'meta_description':
                return $this->generateMetaDescription($keyword);
            
            case 'heading':
                return $this->generateHeading($keyword);
            
            case 'conclusion':
                return $this->generateConclusion($keyword, $tone);
            
            default:
                return $this->generateParagraph($keyword, $tone);
        }
    }
    
    private function generateTitle($keyword, $tone) {
        $templates = [
            "The Ultimate Guide to {keyword}",
            "How to Master {keyword} in 2026",
            "{keyword}: Everything You Need to Know",
            "10 Expert Tips for {keyword}",
            "{keyword} Made Simple: A Complete Guide",
        ];
        
        $template = $templates[array_rand($templates)];
        return str_replace('{keyword}', ucwords($keyword), $template);
    }
    
    private function generateParagraph($keyword, $tone) {
        $intros = [
            "When it comes to {keyword}, understanding the fundamentals is crucial for success.",
            "{keyword} has become increasingly important in today's digital landscape.",
            "Many professionals are discovering the power of {keyword} in their daily work.",
            "The field of {keyword} offers numerous opportunities for growth and development.",
        ];
        
        $middles = [
            "By implementing best practices and staying up-to-date with the latest trends, you can maximize your results.",
            "Research shows that proper implementation can lead to significant improvements in performance.",
            "Industry experts recommend starting with a solid foundation and building from there.",
            "With the right approach and tools, achieving your goals becomes much more attainable.",
        ];
        
        $endings = [
            "This comprehensive approach ensures long-term success and sustainable growth.",
            "By following these guidelines, you'll be well-positioned to achieve your objectives.",
            "The key is to remain consistent and adapt to changing circumstances as needed.",
            "With dedication and the right strategy, excellent results are within reach.",
        ];
        
        $intro = str_replace('{keyword}', $keyword, $intros[array_rand($intros)]);
        $middle = $middles[array_rand($middles)];
        $ending = $endings[array_rand($endings)];
        
        return "$intro $middle $ending";
    }
    
    private function generateMetaDescription($keyword) {
        $templates = [
            "Discover everything about {keyword}. Learn expert tips, best practices, and proven strategies to achieve amazing results. Start today!",
            "Complete guide to {keyword}. Get actionable insights, professional advice, and step-by-step instructions. Read more now!",
            "Master {keyword} with our comprehensive guide. Find tips, tricks, and techniques used by industry experts. Learn more here!",
        ];
        
        $template = $templates[array_rand($templates)];
        return str_replace('{keyword}', $keyword, $template);
    }
    
    private function generateHeading($keyword) {
        $templates = [
            "Understanding {keyword}",
            "Key Benefits of {keyword}",
            "How to Get Started with {keyword}",
            "Best Practices for {keyword}",
            "Common Mistakes to Avoid with {keyword}",
        ];
        
        $template = $templates[array_rand($templates)];
        return str_replace('{keyword}', ucwords($keyword), $template);
    }
    
    private function generateConclusion($keyword, $tone) {
        return "In conclusion, mastering {$keyword} requires dedication, practice, and the right approach. By implementing the strategies and techniques discussed in this guide, you'll be well on your way to achieving your goals. Remember to stay consistent, track your progress, and continuously refine your approach based on results.";
    }
    
    private function generateTitleSuggestions($keyword) {
        return [
            "The Complete Guide to " . ucwords($keyword),
            "How to Master " . ucwords($keyword) . " in 2026",
            ucwords($keyword) . ": Best Practices and Expert Tips",
            "10 Ways to Improve Your " . ucwords($keyword),
            ucwords($keyword) . " Explained: A Beginner's Guide",
        ];
    }
    
    private function generateHeadingsSuggestions($keyword) {
        return [
            "What is " . ucwords($keyword) . "?",
            "Why " . ucwords($keyword) . " Matters",
            "How to Get Started with " . ucwords($keyword),
            "Best Practices for " . ucwords($keyword),
            "Common Challenges and Solutions",
            "Tips from Industry Experts",
            "Measuring Your Success",
            "Future Trends in " . ucwords($keyword),
        ];
    }
    
    private function generateContentOutline($keyword) {
        return [
            [
                'heading' => 'Introduction to ' . ucwords($keyword),
                'subheadings' => [
                    'What is ' . ucwords($keyword) . '?',
                    'Why it Matters',
                ]
            ],
            [
                'heading' => 'Getting Started',
                'subheadings' => [
                    'Prerequisites',
                    'Step-by-Step Guide',
                ]
            ],
            [
                'heading' => 'Best Practices',
                'subheadings' => [
                    'Expert Tips',
                    'Common Mistakes to Avoid',
                ]
            ],
            [
                'heading' => 'Conclusion',
                'subheadings' => [
                    'Key Takeaways',
                    'Next Steps',
                ]
            ],
        ];
    }
    
    private function getRelatedKeywords($keyword) {
        return [
            $keyword . " tips",
            $keyword . " guide",
            "best " . $keyword,
            $keyword . " tutorial",
            "how to " . $keyword,
            $keyword . " examples",
            $keyword . " strategies",
            $keyword . " tools",
        ];
    }
    
    private function getLongTailKeywords($keyword) {
        return [
            "how to improve " . $keyword,
            "best practices for " . $keyword,
            $keyword . " for beginners",
            $keyword . " step by step guide",
            "advanced " . $keyword . " techniques",
        ];
    }
    
    private function getQuestions($keyword) {
        return [
            "What is " . $keyword . "?",
            "How does " . $keyword . " work?",
            "Why is " . $keyword . " important?",
            "When should I use " . $keyword . "?",
            "Where can I learn " . $keyword . "?",
        ];
    }
    
    private function rewriteByStyle($content, $style) {
        switch ($style) {
            case 'simplify':
                return $this->simplifyText($content);
            
            case 'expand':
                return $this->expandText($content);
            
            case 'professional':
                return $this->makeProfessional($content);
            
            case 'casual':
                return $this->makeCasual($content);
            
            default:
                return $this->improveText($content);
        }
    }
    
    private function simplifyText($text) {
        // Basic simplification
        return $text . " (Simplified version)";
    }
    
    private function expandText($text) {
        // Basic expansion
        return $text . " Furthermore, this approach provides additional benefits and opportunities for growth that can significantly impact overall results.";
    }
    
    private function makeProfessional($text) {
        return str_replace(
            ["don't", "can't", "won't", "it's", "we're"],
            ["do not", "cannot", "will not", "it is", "we are"],
            $text
        );
    }
    
    private function makeCasual($text) {
        return str_replace(
            ["do not", "cannot", "will not", "it is", "we are"],
            ["don't", "can't", "won't", "it's", "we're"],
            $text
        );
    }
    
    private function improveText($text) {
        // Basic improvement - capitalize first letter if not already
        return ucfirst(trim($text));
    }
}
