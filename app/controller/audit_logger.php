<?php

/**
 * Audit Logger - Tracks system activities for monitoring and security
 */

function logAuditEvent(PDO $conn, string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): bool
{
    try {
        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? 'system';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 255);

        $stmt = $conn->prepare("
            INSERT INTO audit_logs (user_id, username, action, entity_type, entity_id, details, ip_address, user_agent)
            VALUES (:uid, :uname, :action, :etype, :eid, :details, :ip, :ua)
        ");

        return $stmt->execute([
            ':uid' => $userId,
            ':uname' => $username,
            ':action' => $action,
            ':etype' => $entityType,
            ':eid' => $entityId,
            ':details' => $details,
            ':ip' => $ipAddress,
            ':ua' => $userAgent
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

function getAuditLogs(PDO $conn, array $filters = [], int $limit = 100, int $offset = 0): array
{
    $sql = "SELECT al.*, u.firstName, u.lastName 
            FROM audit_logs al 
            LEFT JOIN users u ON u.id = al.user_id 
            WHERE 1=1";
    $params = [];

    if (!empty($filters['user_id'])) {
        $sql .= " AND al.user_id = :user_id";
        $params[':user_id'] = (int)$filters['user_id'];
    }
    if (!empty($filters['action'])) {
        $sql .= " AND al.action LIKE :action";
        $params[':action'] = '%' . $filters['action'] . '%';
    }
    if (!empty($filters['entity_type'])) {
        $sql .= " AND al.entity_type = :entity_type";
        $params[':entity_type'] = $filters['entity_type'];
    }
    if (!empty($filters['date_from'])) {
        $sql .= " AND DATE(al.created_at) >= :date_from";
        $params[':date_from'] = $filters['date_from'];
    }
    if (!empty($filters['date_to'])) {
        $sql .= " AND DATE(al.created_at) <= :date_to";
        $params[':date_to'] = $filters['date_to'];
    }

    $sql .= " ORDER BY al.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAuditStats(PDO $conn): array
{
    $stats = [];
    $stats['total'] = (int)$conn->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
    $stats['today'] = (int)$conn->query("SELECT COUNT(*) FROM audit_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $stats['this_week'] = (int)$conn->query("SELECT COUNT(*) FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();

    $topActions = $conn->query("
        SELECT action, COUNT(*) as cnt 
        FROM audit_logs 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY action 
        ORDER BY cnt DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    $stats['top_actions'] = $topActions;

    return $stats;
}
