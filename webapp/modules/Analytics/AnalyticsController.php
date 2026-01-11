<?php
/**
 * Analytics Controller
 * Tracks keywords, pages, clicks, impressions
 */

namespace RankMathWebapp\Modules\Analytics;

use RankMathWebapp\Core\Database;
use RankMathWebapp\Core\Response;

class AnalyticsController {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get dashboard overview stats
     */
    public function getDashboardStats($params) {
        $prefix = $this->db->getPrefix();
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d');
        
        // Get summary stats
        $keywordSql = "SELECT 
                        SUM(impressions) as total_impressions,
                        SUM(clicks) as total_clicks,
                        COUNT(DISTINCT keyword) as total_keywords,
                        AVG(position) as avg_position
                       FROM {$prefix}analytics_keywords
                       WHERE date BETWEEN :start_date AND :end_date";
        
        $keywordStats = $this->db->fetchOne($keywordSql, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        // Get SEO score (average from recent analyses)
        $seoSql = "SELECT AVG(score) as avg_score
                   FROM {$prefix}seo_analysis
                   WHERE analyzed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $seoStats = $this->db->fetchOne($seoSql, []);
        
        // Get recent activity
        $activitySql = "SELECT 
                         'SEO Analysis' as type,
                         url,
                         score,
                         analyzed_at as date
                       FROM {$prefix}seo_analysis
                       ORDER BY analyzed_at DESC
                       LIMIT 5";
        
        $activities = $this->db->fetchAll($activitySql, []);
        
        $recent_activity = [];
        foreach ($activities as $activity) {
            $recent_activity[] = "Analyzed {$activity['url']} - Score: {$activity['score']}/100";
        }
        
        if (empty($recent_activity)) {
            $recent_activity[] = "No recent activity";
        }
        
        $data = [
            'seo_score' => round($seoStats['avg_score'] ?? 0),
            'total_keywords' => $keywordStats['total_keywords'] ?? 0,
            'impressions' => $keywordStats['total_impressions'] ?? 0,
            'clicks' => $keywordStats['total_clicks'] ?? 0,
            'avg_position' => round($keywordStats['avg_position'] ?? 0, 1),
            'recent_activity' => $recent_activity,
        ];
        
        Response::success('Dashboard stats retrieved', $data);
    }
    
    /**
     * Get analytics dashboard data
     */
    public function getDashboard($params) {
        $projectId = $_GET['project_id'] ?? null;
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $data = [
            'keywords' => $this->getTopKeywords($projectId, $startDate, $endDate),
            'pages' => $this->getTopPages($projectId, $startDate, $endDate),
            'summary' => $this->getSummary($projectId, $startDate, $endDate),
            'chart_data' => $this->getChartData($projectId, $startDate, $endDate),
        ];
        
        Response::success('Dashboard data retrieved', $data);
    }
    
    /**
     * Add keyword tracking data
     */
    public function addKeyword($params) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $required = ['keyword', 'date'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::error("Field '$field' is required");
            }
        }
        
        $keywordId = $this->db->insert('analytics_keywords', [
            'project_id' => $data['project_id'] ?? null,
            'keyword' => $data['keyword'],
            'impressions' => $data['impressions'] ?? 0,
            'clicks' => $data['clicks'] ?? 0,
            'position' => $data['position'] ?? null,
            'ctr' => $data['ctr'] ?? null,
            'date' => $data['date'],
        ]);
        
        Response::success('Keyword added', ['id' => $keywordId]);
    }
    
    /**
     * Get top keywords
     */
    private function getTopKeywords($projectId, $startDate, $endDate) {
        $prefix = $this->db->getPrefix();
        $sql = "SELECT keyword, 
                       SUM(impressions) as total_impressions,
                       SUM(clicks) as total_clicks,
                       AVG(position) as avg_position,
                       AVG(ctr) as avg_ctr
                FROM {$prefix}analytics_keywords
                WHERE date BETWEEN :start_date AND :end_date";
        
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
        
        if ($projectId) {
            $sql .= " AND project_id = :project_id";
            $params['project_id'] = $projectId;
        }
        
        $sql .= " GROUP BY keyword
                  ORDER BY total_impressions DESC
                  LIMIT 20";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get top pages
     */
    private function getTopPages($projectId, $startDate, $endDate) {
        $prefix = $this->db->getPrefix();
        $sql = "SELECT url,
                       SUM(pageviews) as total_pageviews,
                       SUM(unique_visitors) as total_visitors,
                       SUM(impressions) as total_impressions,
                       SUM(clicks) as total_clicks,
                       AVG(position) as avg_position
                FROM {$prefix}analytics_pages
                WHERE date BETWEEN :start_date AND :end_date";
        
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
        
        if ($projectId) {
            $sql .= " AND project_id = :project_id";
            $params['project_id'] = $projectId;
        }
        
        $sql .= " GROUP BY url
                  ORDER BY total_pageviews DESC
                  LIMIT 20";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get summary statistics
     */
    private function getSummary($projectId, $startDate, $endDate) {
        $prefix = $this->db->getPrefix();
        
        // Keywords summary
        $keywordSql = "SELECT 
                        SUM(impressions) as total_impressions,
                        SUM(clicks) as total_clicks,
                        AVG(position) as avg_position,
                        COUNT(DISTINCT keyword) as total_keywords
                       FROM {$prefix}analytics_keywords
                       WHERE date BETWEEN :start_date AND :end_date";
        
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
        
        if ($projectId) {
            $keywordSql .= " AND project_id = :project_id";
            $params['project_id'] = $projectId;
        }
        
        $keywordStats = $this->db->fetchOne($keywordSql, $params);
        
        // Pages summary
        $pageSql = "SELECT 
                     SUM(pageviews) as total_pageviews,
                     SUM(unique_visitors) as total_visitors,
                     COUNT(DISTINCT url) as total_pages
                    FROM {$prefix}analytics_pages
                    WHERE date BETWEEN :start_date AND :end_date";
        
        if ($projectId) {
            $pageSql .= " AND project_id = :project_id";
        }
        
        $pageStats = $this->db->fetchOne($pageSql, $params);
        
        return array_merge($keywordStats ?: [], $pageStats ?: []);
    }
    
    /**
     * Get chart data for date range
     */
    private function getChartData($projectId, $startDate, $endDate) {
        $prefix = $this->db->getPrefix();
        $sql = "SELECT 
                    date,
                    SUM(impressions) as impressions,
                    SUM(clicks) as clicks,
                    AVG(position) as position
                FROM {$prefix}analytics_keywords
                WHERE date BETWEEN :start_date AND :end_date";
        
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
        
        if ($projectId) {
            $sql .= " AND project_id = :project_id";
            $params['project_id'] = $projectId;
        }
        
        $sql .= " GROUP BY date ORDER BY date ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Import Google Search Console data
     */
    public function importGSC($params) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['keywords'])) {
            Response::error('No keyword data provided');
        }
        
        $imported = 0;
        foreach ($data['keywords'] as $item) {
            $this->db->insert('analytics_keywords', [
                'project_id' => $data['project_id'] ?? null,
                'keyword' => $item['keyword'],
                'impressions' => $item['impressions'] ?? 0,
                'clicks' => $item['clicks'] ?? 0,
                'position' => $item['position'] ?? null,
                'ctr' => $item['ctr'] ?? null,
                'date' => $item['date'],
            ]);
            $imported++;
        }
        
        Response::success("Imported $imported keywords", ['count' => $imported]);
    }
}
