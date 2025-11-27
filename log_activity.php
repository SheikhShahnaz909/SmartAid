<?php
// log_activity.php
// Simple helper to write to activity_logs table.

if (!function_exists('log_activity')) {
    function log_activity(PDO $pdo, ?int $userId, string $role, string $action, string $details = '')
    {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO activity_logs (user_id, role, action, details, ip_address, created_at)
                VALUES (:user_id, :role, :action, :details, :ip, NOW())
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':role'    => $role,
                ':action'  => $action,
                ':details' => $details,
                ':ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Exception $e) {
            error_log('log_activity failed: '.$e->getMessage());
        }
    }
}
