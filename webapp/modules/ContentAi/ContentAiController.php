<?php
/**
 * Content AI Controller
 * AI-powered content generation assistant
 */

namespace RankMathWebapp\Modules\ContentAi;

use RankMathWebapp\Core\Database;
use RankMathWebapp\Core\Response;

class ContentAiController {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Generate content suggestions
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
        
        // Generate content based on type
        $generatedContent = $this->generateByType($keyword, $contentType, $tone);
        
        // Store in history
        $contentId = $this->db->insert('content_ai', [
            'project_id' => $projectId,
            'keyword' => $keyword,
            'content_type' => $contentType,
            'prompt' => $data['prompt'] ?? null,
            'generated_content' => $generatedContent,
            'credits_used' => 1,
        ]);
        
        Response::success('Content generated', [
            'id' => $contentId,
            'content' => $generatedContent,
            'keyword' => $keyword,
            'content_type' => $contentType,
        ]);
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
     * Keyword research
     */
    public function researchKeyword($params) {
        $keyword = $_GET['keyword'] ?? '';
        
        if (empty($keyword)) {
            Response::error('Keyword is required');
        }
        
        $research = [
            'keyword' => $keyword,
            'related_keywords' => $this->getRelatedKeywords($keyword),
            'long_tail_keywords' => $this->getLongTailKeywords($keyword),
            'questions' => $this->getQuestions($keyword),
            'search_volume' => rand(100, 10000), // Simulated
            'difficulty' => rand(20, 80), // Simulated
            'cpc' => number_format(rand(50, 500) / 100, 2), // Simulated
        ];
        
        Response::success('Keyword research complete', $research);
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
