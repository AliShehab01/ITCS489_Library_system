<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "view/HomePage-EN.php");
    exit;
}

$db = new Database();
$conn = $db->conn;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Check password - support both hashed and plain text (for backward compatibility)
                $passwordValid = false;

                // First try password_verify for hashed passwords
                if (password_verify($password, $user['password'])) {
                    $passwordValid = true;
                }
                // Fall back to plain text comparison (for old accounts)
                elseif ($password === $user['password']) {
                    $passwordValid = true;

                    // Upgrade to hashed password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE users SET password = :pwd WHERE id = :id");
                    $updateStmt->execute([':pwd' => $hashedPassword, ':id' => $user['id']]);
                }

                if ($passwordValid) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['first_name'] = $user['firstName'];
                    $_SESSION['last_name'] = $user['lastName'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $user['email'];

                    // Log login event if audit logger exists
                    if (file_exists(__DIR__ . '/../controller/audit_logger.php')) {
                        require_once __DIR__ . '/../controller/audit_logger.php';
                        logAuditEvent($conn, 'LOGIN', 'user', $user['id'], 'User logged in successfully');
                    }

                    // Redirect to home page
                    header("Location: " . BASE_URL . "view/HomePage-EN.php");
                    exit;
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error = "Login failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .login-card {
            max-width: 400px;
            width: 100%;
        }

        .back-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .back-link:hover {
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Back to home link - Fixed path -->
        <div class="text-center mb-3">
            <a href="/public/index.php" class="back-link">
                ← Back to Library Home
            </a>
        </div>

        <div class="login-card mx-auto">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h3 class="mb-0">📚 Library Login</h3>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required autofocus
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    Don't have an account? <a href="<?= BASE_URL ?>view/signup.php" class="fw-semibold">Sign up here</a>
                </div>
            </div>

            <!-- Demo credentials -->
            <div class="card mt-3 bg-light">
                <div class="card-body py-2 text-center">
                    <small class="text-muted">
                        <strong>Demo accounts:</strong><br>
                        admin/admin • staff/staff • vipstudent/vipstudent • student/student
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>