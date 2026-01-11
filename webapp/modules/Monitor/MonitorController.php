<?php
/**
 * 404 Monitor Controller
 * Track and manage 404 errors
 */

namespace RankMathWebapp\Modules\Monitor;

use RankMathWebapp\Core\Database;
use RankMathWebapp\Core\Response;

class MonitorController {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get 404 logs
     */
    public function getLogs($params) {
        $projectId = $_GET['project_id'] ?? null;
        $page = $_GET['page'] ?? 1;
        $perPage = $_GET['per_page'] ?? 20;
        $orderBy = $_GET['order_by'] ?? 'last_accessed';
        $order = $_GET['order'] ?? 'DESC';
        
        $offset = ($page - 1) * $perPage;
        
        $prefix = $this->db->getPrefix();
        $sql = "SELECT * FROM {$prefix}404_monitor WHERE 1=1";
        $params = [];
        
        if ($projectId) {
            $sql .= " AND project_id = :project_id";
            $params['project_id'] = $projectId;
        }
        
        $sql .= " ORDER BY $orderBy $order LIMIT $perPage OFFSET $offset";
        
        $logs = $this->db->fetchAll($sql, $params);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$prefix}404_monitor WHERE 1=1";
        if ($projectId) {
            $countSql .= " AND project_id = :project_id";
        }
        $totalRow = $this->db->fetchOne($countSql, $params);
        $total = $totalRow['total'];
        
        Response::success('404 logs retrieved', [
            'logs' => $logs,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$perPage,
                'total' => (int)$total,
                'total_pages' => ceil($total / $perPage),
            ]
        ]);
    }
    
    /**
     * Log a 404 error
     */
    public function log404($params) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $uri = $data['uri'] ?? '';
        $projectId = $data['project_id'] ?? null;
        
        if (empty($uri)) {
            Response::error('URI is required');
        }
        
        $prefix = $this->db->getPrefix();
        
        // Check if URI already exists
        $existing = $this->db->fetchOne(
            "SELECT id, hits FROM {$prefix}404_monitor 
             WHERE uri = :uri AND project_id = :project_id",
            ['uri' => $uri, 'project_id' => $projectId]
        );
        
        if ($existing) {
            // Update hits and last accessed
            $this->db->update('404_monitor', 
                ['hits' => $existing['hits'] + 1, 'last_accessed' => date('Y-m-d H:i:s')],
                'id = :id',
                ['id' => $existing['id']]
            );
            $id = $existing['id'];
        } else {
            // Insert new record
            $id = $this->db->insert('404_monitor', [
                'project_id' => $projectId,
                'uri' => $uri,
                'referer' => $data['referer'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
                'hits' => 1,
                'last_accessed' => date('Y-m-d H:i:s'),
            ]);
        }
        
        Response::success('404 logged', ['id' => $id]);
    }
    
    /**
     * Delete 404 log
     */
    public function deleteLog($params) {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Log ID is required');
        }
        
        $this->db->delete('404_monitor', 'id = :id', ['id' => $id]);
        
        Response::success('404 log deleted');
    }
    
    /**
     * Clear all 404 logs
     */
    public function clearLogs($params) {
        $projectId = $_POST['project_id'] ?? null;
        
        if ($projectId) {
            $this->db->delete('404_monitor', 'project_id = :project_id', ['project_id' => $projectId]);
        } else {
            $prefix = $this->db->getPrefix();
            $this->db->query("TRUNCATE TABLE {$prefix}404_monitor");
        }
        
        Response::success('404 logs cleared');
    }
    
    /**
     * Export 404 logs as CSV
     */
    public function exportCSV($params) {
        $projectId = $_GET['project_id'] ?? null;
        
        $prefix = $this->db->getPrefix();
        $sql = "SELECT * FROM {$prefix}404_monitor";
        $queryParams = [];
        
        if ($projectId) {
            $sql .= " WHERE project_id = :project_id";
            $queryParams['project_id'] = $projectId;
        }
        
        $sql .= " ORDER BY last_accessed DESC";
        
        $logs = $this->db->fetchAll($sql, $queryParams);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="404-logs-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Headers
        fputcsv($output, ['ID', 'URI', 'Referer', 'Hits', 'Last Accessed', 'Created At']);
        
        // Data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['uri'],
                $log['referer'],
                $log['hits'],
                $log['last_accessed'],
                $log['created_at'],
            ]);
        }
        
        fclose($output);
        exit;
    }
}
