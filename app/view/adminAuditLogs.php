<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../controller/audit_logger.php';

$db = new Database();
$conn = $db->conn;

// Filters
$filters = [
    'user_id' => $_GET['user_id'] ?? null,
    'action' => $_GET['action'] ?? null,
    'entity_type' => $_GET['entity_type'] ?? null,
    'date_from' => $_GET['date_from'] ?? null,
    'date_to' => $_GET['date_to'] ?? null,
];

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$logs = getAuditLogs($conn, $filters, $limit, $offset);
$stats = getAuditStats($conn);

// Get users for filter dropdown
$users = $conn->query("SELECT id, username FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

// Get distinct actions for filter
$actions = $conn->query("SELECT DISTINCT action FROM audit_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 56px;
            background: #f8f9fa;
        }

        .log-details {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>📊 Audit Logs & System Monitoring</h1>
            <a href="<?= BASE_URL ?>view/AdminArea.php" class="btn btn-outline-secondary">← Back to Admin</a>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <div class="card-body py-3">
                        <h3 class="text-primary mb-0"><?= number_format($stats['total']) ?></h3>
                        <small class="text-muted">Total Events</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <div class="card-body py-3">
                        <h3 class="text-success mb-0"><?= number_format($stats['today']) ?></h3>
                        <small class="text-muted">Today</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <div class="card-body py-3">
                        <h3 class="text-info mb-0"><?= number_format($stats['this_week']) ?></h3>
                        <small class="text-muted">This Week</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body py-2">
                        <small class="text-muted d-block mb-1">Top Actions (30d)</small>
                        <?php foreach (array_slice($stats['top_actions'], 0, 3) as $ta): ?>
                            <span class="badge bg-secondary me-1"><?= htmlspecialchars($ta['action']) ?> (<?= $ta['cnt'] ?>)</span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">User</label>
                        <select name="user_id" class="form-select">
                            <option value="">All users</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= $filters['user_id'] == $u['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Action</label>
                        <select name="action" class="form-select">
                            <option value="">All actions</option>
                            <?php foreach ($actions as $a): ?>
                                <option value="<?= htmlspecialchars($a) ?>" <?= $filters['action'] == $a ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Entity Type</label>
                        <input type="text" name="entity_type" class="form-control"
                            value="<?= htmlspecialchars($filters['entity_type'] ?? '') ?>" placeholder="e.g., book, user">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control"
                            value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control"
                            value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Audit Log Entries</span>
                <span class="badge bg-secondary"><?= count($logs) ?> shown</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($logs)): ?>
                    <div class="p-4 text-center text-muted">No audit logs found matching your filters.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>Details</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><small><?= date('M d, H:i:s', strtotime($log['created_at'])) ?></small></td>
                                        <td>
                                            <strong><?= htmlspecialchars($log['username'] ?? 'system') ?></strong>
                                            <?php if ($log['firstName']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($log['firstName'] . ' ' . $log['lastName']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($log['action']) ?></span></td>
                                        <td>
                                            <?php if ($log['entity_type']): ?>
                                                <?= htmlspecialchars($log['entity_type']) ?>
                                                <?php if ($log['entity_id']): ?>
                                                    <small class="text-muted">#<?= $log['entity_id'] ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="log-details" title="<?= htmlspecialchars($log['details'] ?? '') ?>">
                                            <?= htmlspecialchars($log['details'] ?? '-') ?>
                                        </td>
                                        <td><small class="text-muted"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <nav>
                    <ul class="pagination pagination-sm mb-0 justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>">Previous</a>
                            </li>
                        <?php endif; ?>
                        <li class="page-item active"><span class="page-link">Page <?= $page ?></span></li>
                        <?php if (count($logs) >= $limit): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>