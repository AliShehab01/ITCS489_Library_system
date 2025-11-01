<?php
require '../models/dbconnect.php'; // uses PDO

$db = new Database();
$conn = $db->conn;

// Mark a notification as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
  $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
  if ($id) {
    $u = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
    $u->execute([':id' => $id]);
  }
}

// Load notifications (newest first)
$sql = "
  SELECT n.id, n.message, n.is_read, n.created_at,
         u.username, b.title
  FROM notifications n
  JOIN users u ON u.id = n.user_id
  JOIN books b ON b.id = n.book_id
  ORDER BY n.created_at DESC
";

$stmt = $conn->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Notifications</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
  <div class="container py-4">
    <h2 class="mb-3">Notifications</h2>

    <?php if (!$rows): ?>
      <div class="alert alert-secondary">No notifications.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm bg-white align-middle">
          <thead>
            <tr>
              <th>User</th>
              <th>Book</th>
              <th>Message</th>
              <th>Time</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['username']) ?></td>
                <td><?= htmlspecialchars($r['title']) ?></td>
                <td><?= htmlspecialchars($r['message']) ?></td>
                <td><?= htmlspecialchars($r['created_at']) ?></td>
                <td><?= $r['is_read'] ? 'Read' : 'Unread' ?></td>
                <td>
                  <?php if (!$r['is_read']): ?>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                      <button class="btn btn-sm btn-outline-primary" name="mark_read" value="1">Mark
                        read</button>
                    </form>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <a href="AdminArea.php" class="btn btn-outline-secondary mt-3">Back</a>
  </div>
</body>

</html>