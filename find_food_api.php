<?php

// find_food_api.php
require 'config.php';
header('Content-Type: application/json');

// Allow only GET for this API
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error'=>'Method not allowed']);
    exit;
}

// Basic validation
$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
$radiusKm = isset($_GET['radius']) ? floatval($_GET['radius']) : 3.0;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// If no lat/lng provided, perform text-only search
if (empty($_GET['lat']) || empty($_GET['lng'])) {
    // text-only search
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $sql = "SELECT id, name, description, address, lat, lng FROM donations WHERE (available_until IS NULL OR available_until > NOW())";
    $params = [];
    if ($q !== '') {
        $sql .= " AND (name LIKE :q OR description LIKE :q OR address LIKE :q)";
        $params[':q'] = '%' . $q . '%';
    }
    $sql .= " ORDER BY name ASC LIMIT 100";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => (int)$r['id'],
                'name' => $r['name'],
                'description' => $r['description'],
                'address' => $r['address'],
                'lat' => isset($r['lat']) ? (float)$r['lat'] : null,
                'lng' => isset($r['lng']) ? (float)$r['lng'] : null,
                'distance' => null
            ];
        }
        echo json_encode($out);
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error']);
        exit;
    }
}



if ($lat === null || $lng === null) {
    http_response_code(400);
    echo json_encode([]);
    exit;
}

// Haversine formula in SQL to compute distance in KM
// Assumes donations table has columns: id, name, description, address, lat, lng, available_until (DATETIME), created_at
$sql = "
SELECT id, name, description, address, lat, lng,
(6371 * acos(
    cos(radians(:lat)) * cos(radians(lat)) * cos(radians(lng) - radians(:lng))
  + sin(radians(:lat)) * sin(radians(lat))
)) AS distance
FROM donations
WHERE available_until IS NULL OR available_until > NOW()
";

$params = [':lat' => $lat, ':lng' => $lng];

if ($q !== '') {
    // basic text search on name or description or address
    $sql .= " AND (name LIKE :q OR description LIKE :q OR address LIKE :q) ";
    $params[':q'] = '%' . $q . '%';
}

$sql .= " HAVING distance <= :radiusKm
ORDER BY distance ASC
LIMIT 100
";

$params[':radiusKm'] = $radiusKm;

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    // Return numeric values and keep only needed fields
    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'id' => (int)$r['id'],
            'name' => $r['name'],
            'description' => $r['description'],
            'address' => $r['address'],
            'lat' => (float)$r['lat'],
            'lng' => (float)$r['lng'],
            'distance' => (float)$r['distance']
        ];
    }
    echo json_encode($out);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
    exit;
}
