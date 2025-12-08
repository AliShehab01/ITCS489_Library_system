<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';
require_once __DIR__ . '/../controller/policy_helper.php';
require_once __DIR__ . '/../controller/audit_logger.php';

$db = new Database();
$conn = $db->conn;
$message = '';
$messageType = 'info';

// Handle policy updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_policies'])) {
    $updated = 0;
    foreach ($_POST['policy'] as $key => $value) {
        $key = preg_replace('/[^a-z0-9_]/', '', $key);
        $value = trim($value);
        if (updatePolicy($conn, $key, $value)) {
            $updated++;
        }
    }
    logAuditEvent($conn, 'UPDATE_POLICIES', 'system_config', null, "Updated {$updated} policy settings");
    $message = "Updated {$updated} policy setting(s) successfully.";
    $messageType = 'success';
}

$policies = getAllPolicies($conn);

// Group policies by category
$policyGroups = [
    'Loan Duration (Days)' => [],
    'Borrowing Limits' => [],
    'Fines & Renewals' => [],
    'Reservations' => []
];

foreach ($policies as $p) {
    if (strpos($p['config_key'], 'loan_days') === 0) {
        $policyGroups['Loan Duration (Days)'][] = $p;
    } elseif (strpos($p['config_key'], 'borrow_limit') === 0) {
        $policyGroups['Borrowing Limits'][] = $p;
    } elseif (strpos($p['config_key'], 'fine') !== false || strpos($p['config_key'], 'renewal') !== false) {
        $policyGroups['Fines & Renewals'][] = $p;
    } else {
        $policyGroups['Reservations'][] = $p;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing Policies - Admin</title>
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
            <h1>📋 Borrowing Policies</h1>
            <a href="<?= BASE_URL ?>view/AdminArea.php" class="btn btn-outline-secondary">← Back to Admin</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="update_policies" value="1">

            <div class="row g-4">
                <?php foreach ($policyGroups as $groupName => $groupPolicies): ?>
                    <?php if (!empty($groupPolicies)): ?>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><?= htmlspecialchars($groupName) ?></h5>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($groupPolicies as $p): ?>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <?= htmlspecialchars($p['description'] ?: ucwords(str_replace('_', ' ', $p['config_key']))) ?>
                                            </label>
                                            <input type="text"
                                                name="policy[<?= htmlspecialchars($p['config_key']) ?>]"
                                                value="<?= htmlspecialchars($p['config_value']) ?>"
                                                class="form-control">
                                            <small class="text-muted">Key: <?= htmlspecialchars($p['config_key']) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-lg">💾 Save All Policies</button>
            </div>
        </form>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">ℹ️ Policy Information</h5>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li><strong>Loan Duration:</strong> Number of days a user can borrow a book before it's due.</li>
                    <li><strong>Borrowing Limits:</strong> Maximum number of books a user can have borrowed at once.</li>
                    <li><strong>Fine Rate:</strong> Daily charge (in $) for overdue books.</li>
                    <li><strong>Max Renewals:</strong> How many times a loan can be extended.</li>
                    <li><strong>Reservation Limit:</strong> Max active reservations per user.</li>
                    <li><strong>Reservation Expiry:</strong> Days before a "notified" reservation expires if not picked up.</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>