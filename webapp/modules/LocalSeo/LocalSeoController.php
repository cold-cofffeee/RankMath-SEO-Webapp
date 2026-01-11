<?php
/**
 * Local SEO Controller
 * Manage local business locations
 */

namespace RankMathWebapp\Modules\LocalSeo;

use RankMathWebapp\Core\Database;
use RankMathWebapp\Core\Response;

class LocalSeoController {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all locations
     */
    public function getLocations($params) {
        $projectId = $_GET['project_id'] ?? null;
        
        $prefix = $this->db->getPrefix();
        $sql = "SELECT * FROM {$prefix}local_locations";
        $queryParams = [];
        
        if ($projectId) {
            $sql .= " WHERE project_id = :project_id";
            $queryParams['project_id'] = $projectId;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $locations = $this->db->fetchAll($sql, $queryParams);
        
        // Parse JSON business hours
        foreach ($locations as &$location) {
            $location['business_hours'] = json_decode($location['business_hours'], true);
        }
        
        Response::success('Locations retrieved', $locations);
    }
    
    /**
     * Get single location
     */
    public function getLocation($params) {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Location ID is required');
        }
        
        $prefix = $this->db->getPrefix();
        $location = $this->db->fetchOne(
            "SELECT * FROM {$prefix}local_locations WHERE id = :id",
            ['id' => $id]
        );
        
        if (!$location) {
            Response::notFound('Location not found');
        }
        
        $location['business_hours'] = json_decode($location['business_hours'], true);
        
        Response::success('Location retrieved', $location);
    }
    
    /**
     * Add location
     */
    public function addLocation($params) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $required = ['name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::error("Field '$field' is required");
            }
        }
        
        // Geocode address if lat/lng not provided
        if (empty($data['latitude']) || empty($data['longitude'])) {
            $address = $this->buildAddress($data);
            $coords = $this->geocodeAddress($address);
            if ($coords) {
                $data['latitude'] = $coords['lat'];
                $data['longitude'] = $coords['lng'];
            }
        }
        
        $locationId = $this->db->insert('local_locations', [
            'project_id' => $data['project_id'] ?? null,
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'website' => $data['website'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'business_hours' => isset($data['business_hours']) ? json_encode($data['business_hours']) : null,
        ]);
        
        Response::success('Location added', ['id' => $locationId]);
    }
    
    /**
     * Update location
     */
    public function updateLocation($params) {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Location ID is required');
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $updateData = [];
        $allowedFields = ['name', 'address', 'city', 'state', 'country', 'postal_code', 
                          'phone', 'email', 'website', 'latitude', 'longitude', 'business_hours'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'business_hours') {
                    $updateData[$field] = json_encode($data[$field]);
                } else {
                    $updateData[$field] = $data[$field];
                }
            }
        }
        
        if (empty($updateData)) {
            Response::error('No data to update');
        }
        
        $this->db->update('local_locations', $updateData, 'id = :id', ['id' => $id]);
        
        Response::success('Location updated');
    }
    
    /**
     * Delete location
     */
    public function deleteLocation($params) {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Location ID is required');
        }
        
        $this->db->delete('local_locations', 'id = :id', ['id' => $id]);
        
        Response::success('Location deleted');
    }
    
    /**
     * Generate schema markup for location
     */
    public function getSchema($params) {
        $id = $params['id'] ?? null;
        
        if (!$id) {
            Response::error('Location ID is required');
        }
        
        $prefix = $this->db->getPrefix();
        $location = $this->db->fetchOne(
            "SELECT * FROM {$prefix}local_locations WHERE id = :id",
            ['id' => $id]
        );
        
        if (!$location) {
            Response::notFound('Location not found');
        }
        
        $businessHours = json_decode($location['business_hours'], true);
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $location['name'],
        ];
        
        if ($location['address']) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $location['address'],
                'addressLocality' => $location['city'],
                'addressRegion' => $location['state'],
                'postalCode' => $location['postal_code'],
                'addressCountry' => $location['country'],
            ];
        }
        
        if ($location['latitude'] && $location['longitude']) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ];
        }
        
        if ($location['phone']) {
            $schema['telephone'] = $location['phone'];
        }
        
        if ($location['email']) {
            $schema['email'] = $location['email'];
        }
        
        if ($location['website']) {
            $schema['url'] = $location['website'];
        }
        
        if ($businessHours) {
            $schema['openingHoursSpecification'] = $this->formatBusinessHours($businessHours);
        }
        
        Response::success('Schema generated', $schema);
    }
    
    /**
     * Search nearby locations
     */
    public function searchNearby($params) {
        $lat = $_GET['lat'] ?? null;
        $lng = $_GET['lng'] ?? null;
        $radius = $_GET['radius'] ?? 10; // km
        
        if (!$lat || !$lng) {
            Response::error('Latitude and longitude are required');
        }
        
        $prefix = $this->db->getPrefix();
        
        // Haversine formula to find nearby locations
        $sql = "SELECT *,
                (6371 * acos(cos(radians(:lat)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(:lng)) + sin(radians(:lat)) * 
                sin(radians(latitude)))) AS distance
                FROM {$prefix}local_locations
                HAVING distance < :radius
                ORDER BY distance";
        
        $locations = $this->db->fetchAll($sql, [
            'lat' => $lat,
            'lng' => $lng,
            'radius' => $radius
        ]);
        
        Response::success('Nearby locations found', $locations);
    }
    
    private function buildAddress($data) {
        $parts = array_filter([
            $data['address'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['postal_code'] ?? '',
            $data['country'] ?? ''
        ]);
        
        return implode(', ', $parts);
    }
    
    private function geocodeAddress($address) {
        // Simple geocoding using Nominatim (OpenStreetMap)
        $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($address);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'RankMath SEO Webapp');
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if (!empty($data[0])) {
            return [
                'lat' => $data[0]['lat'],
                'lng' => $data[0]['lon']
            ];
        }
        
        return null;
    }
    
    private function formatBusinessHours($hours) {
        $formatted = [];
        
        $dayMap = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];
        
        foreach ($hours as $day => $time) {
            if (!empty($time['open']) && !empty($time['close'])) {
                $formatted[] = [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => $dayMap[$day] ?? ucfirst($day),
                    'opens' => $time['open'],
                    'closes' => $time['close'],
                ];
            }
        }
        
        return $formatted;
    }
}
