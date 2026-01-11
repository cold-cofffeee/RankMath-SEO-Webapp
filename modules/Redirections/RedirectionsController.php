<?php
/**
 * Redirections Controller
 * Manage URL redirections
 */

namespace RankMathWebapp\Modules\Redirections;

use RankMathWebapp\Core\Database;
use RankMathWebapp\Core\Response;

class RedirectionsController {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all redirections
     */
    public function getAll($params) {
        $projectId = $_GET['project_id'] ?? null;
        $status = $_GET['status'] ?? 'active';
        
        $prefix = $this->db->getPrefix();
        $sql = "SELECT * FROM {$prefix}redirections WHERE status = :status";
        $queryParams = ['status' => $status];
        
        if ($projectId) {
            $sql .= " AND project_id = :project_id";
            $queryParams['project_id'] = $projectId;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $redirections = $this->db->fetchAll($sql, $queryParams);
        
        Response::success('Redirections retrieved', $redirections);
    }
    
    /**
     * Add redirection
     */
    public function add($params) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $required = ['source_url', 'target_url'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::error("Field '$field' is required");
            }
        }
        
        // Check if source URL already exists
        $prefix = $this->db->getPrefix();
        $existing = $this->db->fetchOne(
            "SELECT id FROM {$prefix}redirections WHERE source_url = :source_url",
            ['source_url' => $data['source_url']]
        );
        
        if ($existing) {
            Response::error('Redirection for this source URL already exists');
        }
        
        $redirectionId = $this->db->insert('redirections', [
            'project_id' => $data['project_id'] ?? null,
            'source_url' => $data['source_url'],
            'target_url' => $data['target_url'],
            'redirect_type' => $data['redirect_type'] ?? '301',
            'status' => $data['status'] ?? 'active',
        ]);
        
        Response::success('Redirection added', ['id' => $redirectionId]);
    }
    
    /**
     * Update redirection
     */
    public function update($params) {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Redirection ID is required');
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $updateData = [];
        $allowedFields = ['source_url', 'target_url', 'redirect_type', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            Response::error('No data to update');
        }
        
        $this->db->update('redirections', $updateData, 'id = :id', ['id' => $id]);
        
        Response::success('Redirection updated');
    }
    
    /**
     * Delete redirection
     */
    public function delete($params) {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Redirection ID is required');
        }
        
        $this->db->delete('redirections', 'id = :id', ['id' => $id]);
        
        Response::success('Redirection deleted');
    }
    
    /**
     * Check if a URL should be redirected
     */
    public function checkRedirect($params) {
        $url = $_GET['url'] ?? '';
        
        if (empty($url)) {
            Response::error('URL is required');
        }
        
        $prefix = $this->db->getPrefix();
        $redirection = $this->db->fetchOne(
            "SELECT * FROM {$prefix}redirections 
             WHERE source_url = :url AND status = 'active'",
            ['url' => $url]
        );
        
        if ($redirection) {
            // Update hits and last accessed
            $this->db->update('redirections',
                ['hits' => $redirection['hits'] + 1, 'last_accessed' => date('Y-m-d H:i:s')],
                'id = :id',
                ['id' => $redirection['id']]
            );
            
            Response::success('Redirect found', $redirection);
        } else {
            Response::error('No redirect found', 404);
        }
    }
    
    /**
     * Import redirections from CSV
     */
    public function importCSV($params) {
        if (!isset($_FILES['file'])) {
            Response::error('No file uploaded');
        }
        
        $file = $_FILES['file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if (!$handle) {
            Response::error('Failed to open file');
        }
        
        $projectId = $_POST['project_id'] ?? null;
        $imported = 0;
        $errors = [];
        
        // Skip header row
        fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) {
                continue;
            }
            
            $sourceUrl = trim($row[0]);
            $targetUrl = trim($row[1]);
            $redirectType = isset($row[2]) ? trim($row[2]) : '301';
            
            if (empty($sourceUrl) || empty($targetUrl)) {
                $errors[] = "Skipped empty row";
                continue;
            }
            
            try {
                $this->db->insert('redirections', [
                    'project_id' => $projectId,
                    'source_url' => $sourceUrl,
                    'target_url' => $targetUrl,
                    'redirect_type' => $redirectType,
                    'status' => 'active',
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Failed to import $sourceUrl: " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        Response::success("Imported $imported redirections", [
            'imported' => $imported,
            'errors' => $errors
        ]);
    }
}
