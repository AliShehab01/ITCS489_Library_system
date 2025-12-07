<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../controller/checkifadmin.php';
require_once __DIR__ . '/../models/dbconnect.php';

$db = new Database();
$conn = $db->conn;
$flash = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'generate_due':
            $daysAhead = max(1, min(7, (int)($_POST['days_ahead'] ?? 2)));
            $result = generateDueSoonNotifications($conn, $daysAhead);
            $flash[] = sprintf("Created %d due-date reminder(s) out of %d loans due within %d day(s).", $result['created'], $result['examined'], $daysAhead);
            break;
        case 'generate_overdue':
            $result = generateOverdueNotifications($conn);
            $flash[] = sprintf("Created %d overdue alert(s).", $result['created']);
            break;
        case 'generate_reservations':
            $result = generateReservationNotifications($conn);
            $flash[] = sprintf("Notified %d reader(s) about reservation availability.", $result['created']);
            break;
        case 'send_overdue_selected':
            $selected = $_POST['selected_users'] ?? [];
            $ids = array_map('intval', is_array($selected) ? $selected : []);
            if ($ids) {
                $created = 0;
                foreach ($ids as $uid) {
                    $rows = $conn->prepare("SELECT bo.borrow_id, bo.dueDate, b.title FROM borrows bo JOIN books b ON b.id = bo.bookId WHERE bo.user_id = :uid AND bo.isReturned = 'false' AND bo.dueDate < CURDATE()");
                    $rows->execute([':uid' => $uid]);
                    $items = $rows->fetchAll(PDO::FETCH_ASSOC);
                    if (!$items) continue;
                    $lines = array_map(function($r){ return sprintf('%s (due %s)', $r['title'], $r['dueDate']); }, $items);
                    $message = "Overdue items: " . implode('; ', $lines);
                    if (insertNotification($conn, [
                        'user_id' => $uid,
                        'type' => 'overdue',
                        'title' => 'Overdue alert',
                        'message' => $message,
                        'context_type' => 'overdue_summary'
                    ])) {
                        $created++;
                    }
                }
                $flash[] = sprintf("Created overdue alert(s) for %d selected user(s).", $created);
            } else {
                $flash[] = "No users selected.";
            }
            break;
        case 'send_reservation_selected':
            $selected = $_POST['selected_reservations'] ?? [];
            $ids = array_map('intval', is_array($selected) ? $selected : []);
            if ($ids) {
                $created = 0;
                $stmt = $conn->prepare("SELECT r.reservation_id, r.user_id, r.book_id, b.title FROM reservations r JOIN books b ON b.id = r.book_id WHERE r.reservation_id = :rid AND r.status = 'active'");
                foreach ($ids as $rid) {
                    $stmt->execute([':rid' => $rid]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$row) continue;
                    $message = sprintf('Good news! "%s" is ready for pickup.', $row['title']);
                    if (insertNotification($conn, [
                        'user_id' => (int)$row['user_id'],
                        'book_id' => (int)$row['book_id'],
                        'type' => 'reservation',
                        'title' => 'Reservation available',
                        'message' => $message,
                        'context_type' => 'reservation',
                        'context_id' => (int)$row['reservation_id']
                    ])) {
                        $created++;
                        $upd = $conn->prepare("UPDATE reservations SET status = 'notified' WHERE reservation_id = :id");
                        $upd->execute([':id' => (int)$row['reservation_id']]);
                    }
                }
                $flash[] = sprintf("Notified %d selected reservation(s).", $created);
            } else {
                $flash[] = "No reservations selected.";
            }
            break;
        case 'send_announcement':
            $title = trim($_POST['title'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $audience = $_POST['audience'] ?? 'all';
            $targetUser = isset($_POST['target_user']) ? (int)$_POST['target_user'] : null;

            if ($title === '' || $message === '') {
                $flash[] = "Title and message are required for announcements.";
                break;
            }

            if ($audience === 'user' && !$targetUser) {
                $flash[] = "Select a user when sending an announcement to an individual.";
                break;
            }

            $created = createAnnouncementNotifications($conn, $title, $message, $audience, $targetUser);
            $flash[] = sprintf("Announcement delivered to %d user(s).", $created);
            break;
        default:
            $flash[] = "Unknown action.";
    }
}

$stats = getNotificationStats($conn);
$usersList = $conn->query("SELECT id, username FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
$filter = $_GET['type'] ?? 'all';
$notifications = getNotifications($conn, $filter);

// Fetch overdue users summary for admin selection
$overdueUsers = fetchOverdueUsers($conn);
// Fetch active reservation queue entries for admin selection
$reservationQueue = fetchReservationQueue($conn);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Notification Control Center</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <?php if (!defined('STYLE_LOADED')) { define('STYLE_LOADED', true); } ?>
</head>

<body>
    <?php include __DIR__ . '/navbar.php'; ?>

    <main class="page-shell">
        <section class="page-hero mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <p class="text-uppercase text-muted fw-semibold mb-2">Admin</p>
                    <h1 class="display-6 mb-1">Notification Control Center</h1>
                    <p class="text-muted mb-0">Generate reminders, reservation alerts, and announcements in just a few clicks.</p>
                </div>
                <div class="text-end">
                    <div class="badge bg-light text-dark border">Total: <?= (int)$stats['total'] ?></div>
                    <div class="badge bg-warning text-dark">Unread: <?= (int)$stats['unread'] ?></div>
                </div>
            </div>
        </section>

        <?php foreach ($flash as $msg): ?>
            <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
        <?php endforeach; ?>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        Automated reminders
                    </div>
                    <div class="card-body">
                        <form method="POST" class="mb-3">
                            <input type="hidden" name="action" value="generate_due">
                            <label class="form-label">Due soon window (days)</label>
                            <div class="input-group">
                                <input type="number" name="days_ahead" class="form-control" min="1" max="7" value="2">
                                <button class="btn btn-primary">Send reminders</button>
                            </div>
                            <small class="text-muted">Scans active loans due within the window.</small>
                        </form>

                        <hr>

                        <!-- Overdue users selection -->
                        <form method="POST" id="overdueForm" class="mb-3">
                            <input type="hidden" name="action" value="send_overdue_selected">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <strong>Overdue users</strong>
                                <div class="small text-muted"><?= count($overdueUsers) ?> users</div>
                            </div>

                            <?php if (empty($overdueUsers)): ?>
                                <div class="text-muted mb-2">No overdue loans found.</div>
                            <?php else: ?>
                                <div class="list-group mb-2" style="max-height:220px;overflow:auto">
                                    <?php foreach ($overdueUsers as $u): ?>
                                        <label class="list-group-item d-flex flex-column">
                                            <div class="d-flex align-items-center">
                                                <input class="form-check-input me-2" type="checkbox" name="selected_users[]" value="<?= (int)$u['user_id'] ?>">
                                                <div class="flex-grow-1">
                                                    <strong><?= htmlspecialchars($u['username']) ?></strong>
                                                    <div class="small text-muted"><?= (int)$u['overdue_count'] ?> item(s)</div>
                                                </div>
                                            </div>
                                            <div class="small text-muted mt-2">
                                                <?php
                                                    $books = explode('||', $u['books']);
                                                    foreach ($books as $bk) { echo htmlspecialchars($bk) . '<br>'; }
                                                ?>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-sm">Send selected overdue alerts</button>
                                </div>
                            <?php endif; ?>
                        </form>

                        <!-- Send all overdue (separate form to avoid nesting) -->
                        <form method="POST" class="mb-3">
                            <input type="hidden" name="action" value="generate_overdue">
                            <button class="btn btn-outline-danger w-100">Send to all overdue</button>
                            <small class="text-muted">Targets loans past their due date.</small>
                        </form>

                        <hr>

                        <!-- Reservation queue selection -->
                        <form method="POST" id="reservationForm" class="mb-3">
                            <input type="hidden" name="action" value="send_reservation_selected">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <strong>Reservation queue</strong>
                                <div class="small text-muted"><?= count($reservationQueue) ?> entries</div>
                            </div>

                            <?php if (empty($reservationQueue)): ?>
                                <div class="text-muted">No active reservations with available copies.</div>
                            <?php else: ?>
                                <div class="list-group mb-2" style="max-height:220px;overflow:auto">
                                    <?php foreach ($reservationQueue as $r): ?>
                                        <label class="list-group-item d-flex justify-content-between align-items-start">
                                            <div>
                                                <input class="form-check-input me-2" type="checkbox" name="selected_reservations[]" value="<?= (int)$r['reservation_id'] ?>">
                                                <strong><?= htmlspecialchars($r['username']) ?></strong>
                                                <div class="small text-muted"><?= htmlspecialchars($r['title']) ?></div>
                                            </div>
                                            <div class="small text-muted"><?= htmlspecialchars($r['status']) ?></div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <div class="d-flex gap-2">
                                    <button class="btn btn-success btn-sm">Notify selected</button>
                                </div>
                            <?php endif; ?>
                        </form>

                        <!-- Send all reservations -->
                        <form method="POST">
                            <input type="hidden" name="action" value="generate_reservations">
                            <button class="btn btn-outline-secondary w-100">Notify all reservations</button>
                            <small class="text-muted">Alerts the next user whenever a reserved title is available.</small>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-secondary text-white">
                        Announcements & targeted alerts
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="send_announcement">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" maxlength="150"
                                    placeholder="Example: Holiday Closure">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="3"
                                    placeholder="Share hours, closures, policy updates..."></textarea>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Audience</label>
                                    <select name="audience" class="form-select" id="audienceSelect">
                                        <option value="all">All users</option>
                                        <option value="user">Specific user</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Target user (optional)</label>
                                    <select name="target_user" class="form-select">
                                        <option value="">Choose user</option>
                                        <?php foreach ($usersList as $user): ?>
                                            <option value="<?= (int)$user['id'] ?>"><?= htmlspecialchars($user['username']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button class="btn btn-secondary px-4">Send announcement</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <section class="card shadow-sm">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <span class="fw-semibold">Recent notifications</span>
                <div class="btn-group btn-group-sm" role="group">
                    <?php
                    $types = ['all' => 'All', 'due' => 'Due soon', 'overdue' => 'Overdue', 'reservation' => 'Reservations', 'announcement' => 'Announcements'];
                    foreach ($types as $value => $label):
                        $active = $filter === $value ? 'active' : '';
                        ?>
                        <a class="btn btn-outline-primary <?= $active ?>"
                            href="?type=<?= urlencode($value) ?>"><?= htmlspecialchars($label) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (!$notifications): ?>
                    <div class="p-4 text-center text-muted">No notifications to display.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Book / Due date</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notifications as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['username']) ?></td>
                                        <td><span class="badge bg-light text-dark text-uppercase"><?= htmlspecialchars($item['type']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($item['title']) ?></td>
                                        <td>
                                            <?php if (!empty($item['book_title'])): ?>
                                                <div><?= htmlspecialchars($item['book_title']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['due_date'])): ?>
                                                <small class="text-muted">Due: <?= htmlspecialchars($item['due_date']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $item['is_read'] ? 'Read' : 'Unread' ?></td>
                                        <td><?= htmlspecialchars($item['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="app-footer text-center">
        <small>&copy; 2025 Library System. All rights reserved.</small>
    </footer>
</body>

</html>

<?php
function getNotificationStats(PDO $conn): array
{
    $total = (int)$conn->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
    $unread = (int)$conn->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn();
    return ['total' => $total, 'unread' => $unread];
}

function getNotifications(PDO $conn, string $type): array
{
    $sql = "SELECT n.id, n.type, n.title, n.message, n.is_read, n.due_date, n.created_at,
                   u.username, b.title AS book_title
            FROM notifications n
            JOIN users u ON u.id = n.user_id
            LEFT JOIN books b ON b.id = n.book_id";

    $params = [];
    if ($type !== 'all') {
        $sql .= " WHERE n.type = :type";
        $params[':type'] = $type;
    }

    $sql .= " ORDER BY n.created_at DESC LIMIT 50";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateDueSoonNotifications(PDO $conn, int $daysAhead): array
{
    $daysAhead = max(1, $daysAhead);
    $sql = "
        SELECT bo.borrow_id, bo.user_id, bo.bookId, bo.dueDate, b.title
        FROM borrows bo
        JOIN books b ON b.id = bo.bookId
        WHERE bo.isReturned = 'false'
          AND bo.dueDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$daysAhead} DAY)
    ";
    $rows = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $created = 0;

    foreach ($rows as $row) {
        $message = sprintf('Reminder: "%s" is due on %s.', $row['title'], $row['dueDate']);
        if (insertNotification($conn, [
            'user_id' => (int)$row['user_id'],
            'book_id' => (int)$row['bookId'],
            'type' => 'due',
            'title' => 'Due date reminder',
            'message' => $message,
            'due_date' => $row['dueDate'],
            'context_type' => 'borrow',
            'context_id' => (int)$row['borrow_id']
        ])) {
            $created++;
        }
    }

    return ['created' => $created, 'examined' => count($rows)];
}

function generateOverdueNotifications(PDO $conn): array
{
    $sql = "
        SELECT bo.borrow_id, bo.user_id, bo.bookId, bo.dueDate, b.title
        FROM borrows bo
        JOIN books b ON b.id = bo.bookId
        WHERE bo.isReturned = 'false'
          AND bo.dueDate < CURDATE()
    ";

    $rows = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $created = 0;

    foreach ($rows as $row) {
        $message = sprintf('Overdue: "%s" was due on %s. Please return or renew.', $row['title'], $row['dueDate']);
        if (insertNotification($conn, [
            'user_id' => (int)$row['user_id'],
            'book_id' => (int)$row['bookId'],
            'type' => 'overdue',
            'title' => 'Overdue alert',
            'message' => $message,
            'due_date' => $row['dueDate'],
            'context_type' => 'borrow',
            'context_id' => (int)$row['borrow_id']
        ])) {
            $created++;
        }
    }

    return ['created' => $created];
}

function generateReservationNotifications(PDO $conn): array
{
    $sql = "
        SELECT r.reservation_id, r.user_id, r.book_id, b.title
        FROM reservations r
        JOIN books b ON b.id = r.book_id
        WHERE r.status = 'active' AND b.quantity > 0
    ";
    $rows = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $created = 0;

    foreach ($rows as $row) {
        $message = sprintf('Good news! "%s" is ready for pickup.', $row['title']);
        if (insertNotification($conn, [
            'user_id' => (int)$row['user_id'],
            'book_id' => (int)$row['book_id'],
            'type' => 'reservation',
            'title' => 'Reservation available',
            'message' => $message,
            'context_type' => 'reservation',
            'context_id' => (int)$row['reservation_id']
        ])) {
            $created++;
            $upd = $conn->prepare("UPDATE reservations SET status = 'notified' WHERE reservation_id = :id");
            $upd->execute([':id' => (int)$row['reservation_id']]);
        }
    }

    return ['created' => $created];
}

function createAnnouncementNotifications(PDO $conn, string $title, string $message, string $audience, ?int $targetUser): int
{
    $targets = [];
    if ($audience === 'user' && $targetUser) {
        $targets = [$targetUser];
    } else {
        $targets = $conn->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
    }

    $created = 0;
    foreach ($targets as $userId) {
        if (insertNotification($conn, [
            'user_id' => (int)$userId,
            'type' => 'announcement',
            'title' => $title,
            'message' => $message,
            'context_type' => 'announcement'
        ])) {
            $created++;
        }
    }
    return $created;
}

function insertNotification(PDO $conn, array $data): bool
{
    $userId = $data['user_id'] ?? null;
    if (!$userId) {
        return false;
    }

    $contextType = $data['context_type'] ?? null;
    $contextId = $data['context_id'] ?? null;

    if ($contextType && $contextId) {
        $stmt = $conn->prepare("
            SELECT id FROM notifications
            WHERE user_id = :user_id AND type = :type AND context_type = :ctx AND context_id = :ctx_id
            LIMIT 1
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':type' => $data['type'],
            ':ctx' => $contextType,
            ':ctx_id' => $contextId
        ]);
        if ($stmt->fetchColumn()) {
            return false;
        }
    }

    $insert = $conn->prepare("
        INSERT INTO notifications (user_id, book_id, type, title, message, due_date, context_type, context_id)
        VALUES (:user_id, :book_id, :type, :title, :message, :due_date, :context_type, :context_id)
    ");

    return $insert->execute([
        ':user_id' => $userId,
        ':book_id' => $data['book_id'] ?? null,
        ':type' => $data['type'],
        ':title' => $data['title'],
        ':message' => $data['message'],
        ':due_date' => $data['due_date'] ?? null,
        ':context_type' => $contextType,
        ':context_id' => $contextId
    ]);
}

/**
 * Return list of users who have overdue loans, with a simple concatenated list of book titles and due dates.
 * Each row: user_id, username, overdue_count, books (string joined by '||')
 */
function fetchOverdueUsers(PDO $conn): array
{
    $sql = "SELECT u.id AS user_id, u.username, COUNT(*) AS overdue_count, GROUP_CONCAT(CONCAT(b.title, ' (', bo.dueDate, ')') SEPARATOR '||') AS books
            FROM borrows bo
            JOIN users u ON u.id = bo.user_id
            JOIN books b ON b.id = bo.bookId
            WHERE bo.isReturned = 'false' AND bo.dueDate < CURDATE()
            GROUP BY u.id
            ORDER BY u.username";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Return active reservations (status='active') so admin can notify specific entries.
 */
function fetchReservationQueue(PDO $conn): array
{
    $sql = "SELECT r.reservation_id, r.user_id, u.username, r.book_id, b.title, r.status
            FROM reservations r
            JOIN users u ON u.id = r.user_id
            JOIN books b ON b.id = r.book_id
            WHERE r.status = 'active'
            ORDER BY r.reservation_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
