<?php
// api/feed.php

require_once __DIR__ . '/../config.php'; // Make sure config.php has $pdo connection

header('Content-Type: application/json; charset=utf-8');

// Allow CORS if needed (safe for local testing)
header('Access-Control-Allow-Origin: *');

try {
    // Fetch the 50 most recent posts with user names
    $sql = "
        SELECT 
            p.id,
            p.user_id,
            p.caption,
            p.image_path,
            p.created_at,
            u.name AS author_name
        FROM posts p
        LEFT JOIN users u ON u.user_id = p.user_id
        ORDER BY p.created_at DESC
        LIMIT 50
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalize image URLs (assuming images stored in /uploads/posts/)
    foreach ($posts as &$row) {
        if (!empty($row['image_path'])) {
            // Ensure no double slashes
            $cleanPath = ltrim($row['image_path'], '/');
            $row['image_url'] = "/uploads/posts/" . $cleanPath;
        } else {
            $row['image_url'] = null;
        }
    }

    echo json_encode([
        'success' => true,
        'count' => count($posts),
        'data' => $posts
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
}
?>
