<?php
// app/view/notifications.php – Admin: send borrowing alerts & announcements
session_start();

require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';

$db   = new Database();
$conn = $db->conn;

$flash = [];

/* ---------- Helper: create one notification ---------- */
function createNotification(PDO $conn, array $data): bool
{
    $stmt = $conn->prepare("
        INSERT INTO notifications
            (user_id, book_id, type, title, message, context_type, is_read, created_at)
        VALUES
            (:user_id, :book_id, :type, :title, :message, :context_type, 0, NOW())
    ");

    return $stmt->execute([
        ':user_id'      => $data['user_id'],
        ':book_id'      => $data['book_id'] ?? null,
        ':type'         => $data['type'],
        ':title'        => $data['title'],
        ':message'      => $data['message'],
        ':context_type' => $data['context_type'] ?? null,
    ]);
}

/* ---------- Actions (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {

        /* 1) مدموج: Due + Overdue alerts حسب المستخدمين المختارين */
        case 'send_borrowing_alerts':
            $selected = $_POST['selected_users'] ?? [];
            $userIds  = array_map('intval', is_array($selected) ? $selected : []);
            $daysAhead = max(1, min(7, (int)($_POST['days_ahead'] ?? 3)));

            if (!$userIds) {
                $flash[] = "No users selected.";
                break;
            }

            $sql = "
                SELECT
                    b.user_id,
                    bk.title,
                    b.dueDate
                FROM borrows b
                JOIN books bk ON bk.id = b.bookId
                WHERE b.user_id = :uid
                  AND b.isReturned = 'false'
                  AND (
                        b.dueDate < CURDATE()
                        OR (b.dueDate BETWEEN CURDATE()
                                         AND DATE_ADD(CURDATE(), INTERVAL :days DAY))
                  )
                ORDER BY b.dueDate
            ";
            $stmt = $conn->prepare($sql);

            $createdTotal = 0;

            foreach ($userIds as $uid) {
                $stmt->execute([
                    ':uid'  => $uid,
                    ':days' => $daysAhead
                ]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (!$items) continue;

                $dueSoon   = [];
                $overdue   = [];
                foreach ($items as $row) {
                    if ($row['dueDate'] < date('Y-m-d')) {
                        $overdue[] = $row;
                    } else {
                        $dueSoon[] = $row;
                    }
                }

                $lines = [];
                if ($dueSoon) {
                    $lines[] = "Due soon:";
                    foreach ($dueSoon as $r) {
                        $lines[] = sprintf('- "%s" (due %s)',
                            $r['title'], $r['dueDate']);
                    }
                }
                if ($overdue) {
                    $lines[] = ($dueSoon ? "" : "") . "Overdue:";
                    foreach ($overdue as $r) {
                        $lines[] = sprintf('- "%s" (due %s)',
                            $r['title'], $r['dueDate']);
                    }
                }

                $message = implode("\n", $lines);

                // نوع الإشعار: لو فيه متأخر نستخدم overdue، غير كذا due
                $type = $overdue ? 'overdue' : 'due';

                if (createNotification($conn, [
                    'user_id'      => $uid,
                    'book_id'      => null,
                    'type'         => $type,
                    'title'        => 'Borrowing reminder',
                    'message'      => $message,
                    'context_type' => 'borrow_summary',
                ])) {
                    $createdTotal++;
                }
            }

            $flash[] = "Created borrowing alert(s) for $createdTotal user(s).";
            break;

        /* 2) Reservation alerts */
        case 'send_reservation_selected':
            $selected = $_POST['selected_reservations'] ?? [];
            $resIds   = array_map('intval', is_array($selected) ? $selected : []);

            if (!$resIds) {
                $flash[] = "No reservations selected.";
                break;
            }

            $in  = implode(',', array_fill(0, count($resIds), '?'));
            $sql = "
                SELECT r.reservation_id, r.user_id, r.book_id,
                       u.username, bk.title
                FROM reservations r
                JOIN users u ON u.id = r.user_id
                JOIN books bk ON bk.id = r.book_id
                WHERE r.reservation_id IN ($in)
                  AND r.status = 'active'
            ";
            $stmt = $conn->prepare($sql);
            $stmt->execute($resIds);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $created = 0;
            $update  = $conn->prepare("
                UPDATE reservations
                SET status = 'notified'
                WHERE reservation_id = :rid
            ");

            foreach ($rows as $row) {
                $message = sprintf(
                    'Good news! "%s" is now available for pickup.',
                    $row['title']
                );
                if (createNotification($conn, [
                    'user_id'      => (int)$row['user_id'],
                    'book_id'      => (int)$row['book_id'],
                    'type'         => 'reservation',
                    'title'        => 'Reservation available',
                    'message'      => $message,
                    'context_type' => 'reservation_ready',
                ])) {
                    $created++;
                    $update->execute([':rid' => $row['reservation_id']]);
                }
            }

            $flash[] = "Created reservation alert(s) for $created reservation(s).";
            break;

        /* 3) Announcements */
        case 'send_announcement':
            $title    = trim($_POST['title'] ?? '');
            $message  = trim($_POST['message'] ?? '');
            $audience = $_POST['audience'] ?? 'all';
            $targetId = (int)($_POST['target_user_id'] ?? 0);

            if ($title === '' || $message === '') {
                $flash[] = "Title and message are required.";
                break;
            }

            $users = [];
            if ($audience === 'user' && $targetId > 0) {
                $stmt = $conn->prepare("SELECT id AS user_id FROM users WHERE id = :uid");
                $stmt->execute([':uid' => $targetId]);
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $users[] = (int)$row['user_id'];
                }
            } else {
                $rows = $conn->query("SELECT id AS user_id FROM users")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $r) $users[] = (int)$r['user_id'];
            }

            $created = 0;
            foreach ($users as $uid) {
                if (createNotification($conn, [
                    'user_id'      => $uid,
                    'book_id'      => null,
                    'type'         => 'announcement',
                    'title'        => $title,
                    'message'      => $message,
                    'context_type' => 'announcement',
                ])) {
                    $created++;
                }
            }

            $flash[] = "Announcement delivered to $created user(s).";
            break;
    }
}

/* ---------- Data for UI ---------- */
$stats = $conn->query("
    SELECT COUNT(*) AS total,
           SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) AS unread
    FROM notifications
")->fetch(PDO::FETCH_ASSOC);

/* كم يوم نعتبره "due soon" في العرض */
$daysAheadPreview = 3;

/* Users with due soon + overdue counts */
$sqlDue = "
    SELECT
        u.id AS user_id,
        u.username,
        SUM(CASE WHEN b.isReturned = 'false' AND b.dueDate < CURDATE()
                 THEN 1 ELSE 0 END) AS overdue_count,
        SUM(CASE WHEN b.isReturned = 'false'
                     AND b.dueDate >= CURDATE()
                     AND b.dueDate <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
                 THEN 1 ELSE 0 END) AS due_soon_count,
        MIN(CASE WHEN b.isReturned = 'false' THEN b.dueDate END) AS nearest_due
    FROM borrows b
    JOIN users u ON u.id = b.user_id
    GROUP BY u.id, u.username
    HAVING overdue_count > 0 OR due_soon_count > 0
    ORDER BY u.username
";
$stmtDue = $conn->prepare($sqlDue);
$stmtDue->execute([':days' => $daysAheadPreview]);
$borrowingUsers = $stmtDue->fetchAll(PDO::FETCH_ASSOC);

/* حجوزات نشطة */
$reservationsReady = $conn->query("
    SELECT r.reservation_id, r.status, r.reserved_at,
           u.username, u.id AS user_id, bk.title
    FROM reservations r
    JOIN users u ON u.id = r.user_id
    JOIN books bk ON bk.id = r.book_id
    WHERE r.status = 'active'
    ORDER BY r.reserved_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* All users (للاعلانات) */
$usersList = $conn->query("
    SELECT id AS user_id, username
    FROM users
    ORDER BY username
")->fetchAll(PDO::FETCH_ASSOC);

/* Recent notifications */
$recentNotifications = $conn->query("
    SELECT n.id, n.title, n.type, n.created_at,
           u.username
    FROM notifications n
    LEFT JOIN users u ON u.id = n.user_id
    ORDER BY n.created_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Notification Control Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap + theme -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body>
<?php include __DIR__ . '/navbar.php'; ?>

<main class="site-content">
    <!-- Header -->
    <section class="py-4 bg-white border-bottom">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="h4 mb-1">Notification Control Center</h1>
                <p class="text-muted mb-0">
                    Send borrowing alerts (due & overdue), reservation alerts, and library announcements.
                </p>
            </div>
            <div class="text-end small">
                <div class="badge bg-dark">
                    Total: <?= (int)($stats['total'] ?? 0); ?>
                </div>
                <div class="badge bg-warning text-dark">
                    Unread: <?= (int)($stats['unread'] ?? 0); ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Content -->
    <section class="py-4">
        <div class="container my-3">
            <div class="admin-wrapper mx-auto">

                <?php foreach ($flash as $msg): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($msg); ?></div>
                <?php endforeach; ?>

                <!-- Row of cards: 2 أعمدة على الشاشات الكبيرة -->
                <div class="row row-cols-1 row-cols-lg-2 g-4 mb-4">

                    <!-- Borrowing alerts (Due + Overdue معاً وبشكل مفصل) -->
                    <div class="col">
                        <div class="card shadow-custom h-100">
                            <div class="card-header">
                                <h2 class="h6 mb-0">Borrowing alerts (due & overdue)</h2>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-2">
                                    Select users with items <strong>due within the next <?= $daysAheadPreview; ?> day(s)</strong>
                                    or already <strong>overdue</strong>, then send them a detailed summary.
                                </p>

                                <form method="post">
                                    <input type="hidden" name="action" value="send_borrowing_alerts">
                                    <input type="hidden" name="days_ahead" value="<?= $daysAheadPreview; ?>">

                                    <?php if (empty($borrowingUsers)): ?>
                                        <div class="text-muted small">
                                            No users with due or overdue items for the preview range.
                                        </div>
                                    <?php else: ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                                            <span>
                                                Using preview window: next <?= $daysAheadPreview; ?> day(s) for due soon.
                                            </span>
                                        </div>

                                        <div class="list-group mb-2" style="max-height: 260px; overflow:auto;">
                                            <?php foreach ($borrowingUsers as $u): ?>
                                                <label class="list-group-item d-flex align-items-start gap-2">
                                                    <input type="checkbox"
                                                           class="form-check-input mt-1"
                                                           name="selected_users[]"
                                                           value="<?= (int)$u['user_id']; ?>">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between">
                                                            <strong><?= htmlspecialchars($u['username']); ?></strong>
                                                            <span class="small text-muted">
                                                                <?php if ($u['nearest_due']): ?>
                                                                    Nearest due: <?= htmlspecialchars($u['nearest_due']); ?>
                                                                <?php endif; ?>
                                                            </span>
                                                        </div>
                                                        <div class="small text-muted">
                                                            <?= (int)$u['due_soon_count']; ?> due soon
                                                            · <?= (int)$u['overdue_count']; ?> overdue
                                                        </div>
                                                    </div>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>

                                        <button class="btn btn-primary btn-sm">
                                            Send alerts to selected
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Reservation alerts -->
                    <div class="col">
                        <div class="card shadow-custom h-100">
                            <div class="card-header">
                                <h2 class="h6 mb-0">Reservation alerts</h2>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    Notify users when reserved books become available.
                                </p>
                                <form method="post">
                                    <input type="hidden" name="action" value="send_reservation_selected">
                                    <?php if (empty($reservationsReady)): ?>
                                        <div class="text-muted small">No active reservations found.</div>
                                    <?php else: ?>
                                        <div class="list-group mb-2" style="max-height: 260px; overflow:auto;">
                                            <?php foreach ($reservationsReady as $r): ?>
                                                <label class="list-group-item d-flex align-items-center gap-2">
                                                    <input type="checkbox"
                                                           class="form-check-input"
                                                           name="selected_reservations[]"
                                                           value="<?= (int)$r['reservation_id']; ?>">
                                                    <div class="flex-grow-1">
                                                        <strong><?= htmlspecialchars($r['title']); ?></strong>
                                                        <div class="small text-muted">
                                                            <?= htmlspecialchars($r['username']); ?>
                                                            · reserved at <?= htmlspecialchars($r['reserved_at']); ?>
                                                        </div>
                                                    </div>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <button class="btn btn-success btn-sm">
                                            Notify selected
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                </div> <!-- /row -->

                <!-- Announcements card -->
                <div class="card shadow-custom mb-4">
                    <div class="card-header">
                        <h2 class="h6 mb-0">Announcements</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Send important library announcements (holidays, closures, updates).
                        </p>
                        <form method="post" class="row g-3">
                            <input type="hidden" name="action" value="send_announcement">

                            <div class="col-md-6">
                                <label class="form-label small mb-1">Title</label>
                                <input type="text" name="title"
                                       class="form-control form-control-sm"
                                       placeholder="e.g. Holiday hours update" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small mb-1">Audience</label>
                                <select name="audience"
                                        class="form-select form-select-sm"
                                        id="audienceSelect"
                                        onchange="toggleAudienceUser(this.value)">
                                    <option value="all" selected>All users</option>
                                    <option value="user">Specific user…</option>
                                </select>
                            </div>

                            <div class="col-12 d-none" id="audienceUserWrap">
                                <label class="form-label small mb-1">Target user</label>
                                <select name="target_user_id"
                                        class="form-select form-select-sm">
                                    <option value="">Select user…</option>
                                    <?php foreach ($usersList as $u): ?>
                                        <option value="<?= (int)$u['user_id']; ?>">
                                            <?= htmlspecialchars($u['username']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label small mb-1">Message</label>
                                <textarea name="message"
                                          class="form-control form-control-sm"
                                          rows="3"
                                          placeholder="Write your announcement here…" required></textarea>
                            </div>

                            <div class="col-12 text-end">
                                <button class="btn btn-secondary btn-sm px-4">
                                    Send announcement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recent notifications -->
                <div class="card shadow-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h6 mb-0">Recent notifications</h2>
                            <small class="text-muted">Last 10 notifications sent.</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentNotifications)): ?>
                            <div class="p-4 text-center text-muted">
                                No notifications yet.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Created</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($recentNotifications as $n): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($n['username'] ?? 'System'); ?></td>
                                            <td><?= htmlspecialchars($n['title']); ?></td>
                                            <td><?= htmlspecialchars(ucfirst($n['type'])); ?></td>
                                            <td><?= htmlspecialchars($n['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /.admin-wrapper -->
        </div>
    </section>
</main>

<footer class="py-3 mt-4">
    <div class="container text-center small text-muted">
        © 2025 Library System. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleAudienceUser(value) {
        const wrap = document.getElementById('audienceUserWrap');
        if (!wrap) return;
        value === 'user' ? wrap.classList.remove('d-none') : wrap.classList.add('d-none');
    }
</script>
</body>
</html>
