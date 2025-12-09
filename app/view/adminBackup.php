<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../controller/audit_logger.php';

$db = new Database();
$conn = $db->conn;
$message = '';
$messageType = 'info';

$backupDir = __DIR__ . '/../../backups';
if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0755, true);
}

// Handle backup creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_backup'])) {
    $tables = ['users', 'books', 'borrows', 'reservations', 'notifications', 'system_config', 'audit_logs'];
    $backupData = ['created_at' => date('Y-m-d H:i:s'), 'tables' => []];

    try {
        foreach ($tables as $table) {
            $stmt = $conn->query("SELECT * FROM {$table}");
            $backupData['tables'][$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = $backupDir . '/' . $filename;

        if (file_put_contents($filepath, json_encode($backupData, JSON_PRETTY_PRINT))) {
            logAuditEvent($conn, 'CREATE_BACKUP', 'system', null, "Created backup: {$filename}");
            $message = "Backup created successfully: {$filename}";
            $messageType = 'success';
        } else {
            $message = "Failed to write backup file.";
            $messageType = 'danger';
        }
    } catch (PDOException $e) {
        $message = "Backup error: " . $e->getMessage();
        $messageType = 'danger';
    }
}

// Handle backup download
if (isset($_GET['download'])) {
    $file = basename($_GET['download']);
    $filepath = $backupDir . '/' . $file;
    if (file_exists($filepath) && pathinfo($file, PATHINFO_EXTENSION) === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}

// Handle backup deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_backup'])) {
    $file = basename($_POST['delete_backup']);
    $filepath = $backupDir . '/' . $file;
    if (file_exists($filepath) && pathinfo($file, PATHINFO_EXTENSION) === 'json') {
        if (unlink($filepath)) {
            logAuditEvent($conn, 'DELETE_BACKUP', 'system', null, "Deleted backup: {$file}");
            $message = "Backup deleted: {$file}";
            $messageType = 'warning';
        }
    }
}

// Handle restore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_backup'])) {
    $file = basename($_POST['restore_backup']);
    $filepath = $backupDir . '/' . $file;

    if (file_exists($filepath)) {
        $backupData = json_decode(file_get_contents($filepath), true);

        if ($backupData && isset($backupData['tables'])) {
            try {
                $conn->beginTransaction();
                $restored = 0;

                foreach ($backupData['tables'] as $table => $rows) {
                    if (empty($rows)) continue;

                    // Clear existing data
                    $conn->exec("DELETE FROM {$table}");

                    // Insert backup data
                    $columns = array_keys($rows[0]);
                    $placeholders = implode(',', array_fill(0, count($columns), '?'));
                    $columnList = implode(',', array_map(fn($c) => "`{$c}`", $columns));

                    $stmt = $conn->prepare("INSERT INTO {$table} ({$columnList}) VALUES ({$placeholders})");

                    foreach ($rows as $row) {
                        $stmt->execute(array_values($row));
                        $restored++;
                    }
                }

                $conn->commit();
                logAuditEvent($conn, 'RESTORE_BACKUP', 'system', null, "Restored from: {$file}, {$restored} records");
                $message = "Backup restored successfully! {$restored} records restored.";
                $messageType = 'success';
            } catch (PDOException $e) {
                $conn->rollBack();
                $message = "Restore failed: " . $e->getMessage();
                $messageType = 'danger';
            }
        }
    }
}

// Get existing backups
$backups = [];
if (is_dir($backupDir)) {
    $files = glob($backupDir . '/*.json');
    foreach ($files as $file) {
        $backups[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => filemtime($file)
        ];
    }
    usort($backups, fn($a, $b) => $b['date'] - $a['date']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 56px;
            background: #f8f9fa;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>💾 Backup & Restore</h1>
            <a href="<?= BASE_URL ?>view/AdminArea.php" class="btn btn-outline-secondary">← Back to Admin</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Create Backup</h5>
                    </div>
                    <div class="card-body">
                        <p>Create a full backup of all system data including:</p>
                        <ul>
                            <li>Users & permissions</li>
                            <li>Books catalog</li>
                            <li>Borrowing records</li>
                            <li>Reservations</li>
                            <li>Notifications</li>
                            <li>System configuration</li>
                            <li>Audit logs</li>
                        </ul>
                        <form method="POST">
                            <button type="submit" name="create_backup" class="btn btn-success w-100">
                                📥 Create New Backup
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Available Backups</h5>
                        <span class="badge bg-light text-dark"><?= count($backups) ?> backup(s)</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($backups)): ?>
                            <div class="p-4 text-center text-muted">
                                No backups found. Create your first backup above.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Filename</th>
                                            <th>Size</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($backups as $backup): ?>
                                            <tr>
                                                <td><code><?= htmlspecialchars($backup['name']) ?></code></td>
                                                <td><?= number_format($backup['size'] / 1024, 1) ?> KB</td>
                                                <td><?= date('M d, Y H:i', $backup['date']) ?></td>
                                                <td>
                                                    <a href="?download=<?= urlencode($backup['name']) ?>"
                                                        class="btn btn-sm btn-outline-primary">Download</a>
                                                    <form method="POST" style="display:inline"
                                                        onsubmit="return confirm('Restore this backup? This will REPLACE all current data!')">
                                                        <input type="hidden" name="restore_backup" value="<?= htmlspecialchars($backup['name']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-warning">Restore</button>
                                                    </form>
                                                    <form method="POST" style="display:inline"
                                                        onsubmit="return confirm('Delete this backup permanently?')">
                                                        <input type="hidden" name="delete_backup" value="<?= htmlspecialchars($backup['name']) ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-warning mt-4">
            <strong>⚠️ Warning:</strong> Restoring a backup will <strong>replace all current data</strong> with the backup data.
            Make sure to create a new backup before restoring if you want to preserve current data.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>