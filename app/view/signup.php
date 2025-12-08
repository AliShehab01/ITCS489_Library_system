<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../models/dbconnect.php';

$db = new Database();
$conn = $db->conn;

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Validate email domain
    $allowedDomains = ['stu.uob.edu.bh', 'uob.edu.bh'];
    $emailDomain = substr(strrchr($email, "@"), 1);

    if (empty($username) || empty($password) || empty($email)) {
        $error = "Username, email, and password are required.";
    } elseif (!in_array(strtolower($emailDomain), $allowedDomains)) {
        $error = "Only University of Bahrain email addresses (@stu.uob.edu.bh or @uob.edu.bh) are allowed.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Validation
        if (empty($username)) {
            $errors[] = "Username is required.";
        } elseif (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = "Username can only contain letters, numbers, and underscores.";
        }

        // Password validation
        if (empty($password)) {
            $errors[] = "Password is required.";
        } else {
            if (strlen($password) < 6) {
                $errors[] = "Password must be at least 6 characters long.";
            }
            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = "Password must contain at least one uppercase letter.";
            }
            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = "Password must contain at least one lowercase letter.";
            }
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = "Password must contain at least one number.";
            }
        }

        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match.";
        }

        if (empty($firstName)) {
            $errors[] = "First name is required.";
        }

        if (empty($lastName)) {
            $errors[] = "Last name is required.";
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        // Check if username already exists
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            if ($stmt->fetch()) {
                $errors[] = "Username already taken. Please choose another.";
            }
        }

        // Check if email already exists (if provided)
        if (empty($errors) && !empty($email)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = "Email already registered.";
            }
        }

        // Create account if no errors
        if (empty($errors)) {
            try {
                // Hash password for security
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO users (username, password, firstName, lastName, email, phoneNumber, currentNumOfBorrows, role) VALUES (:username, :password, :firstName, :lastName, :email, :phone, 0, 'Student')");

                $stmt->execute([
                    ':username' => $username,
                    ':password' => $hashedPassword,
                    ':firstName' => $firstName,
                    ':lastName' => $lastName,
                    ':email' => $email ?: null,
                    ':phone' => $phoneNumber ?: null
                ]);

                $success = "Account created successfully! You can now login.";

                // Clear form
                $username = $firstName = $lastName = $email = $phoneNumber = '';
            } catch (PDOException $e) {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            min-height: 100vh;
            padding-top: 40px;
            padding-bottom: 40px;
        }

        .signup-card {
            max-width: 520px;
            width: 100%;
        }

        .password-requirements {
            font-size: 0.8rem;
        }

        .requirement {
            color: #dc3545;
            transition: color 0.2s;
        }

        .requirement.valid {
            color: #198754;
        }

        .requirement.valid::before {
            content: "✓ ";
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

        <div class="signup-card mx-auto">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h3 class="mb-1">📚 Create Account</h3>
                    <small class="opacity-75">Join the University Library</small>
                </div>
                <div class="card-body p-4">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <h5 class="alert-heading">🎉 Success!</h5>
                            <?= htmlspecialchars($success) ?>
                            <hr>
                            <a href="<?= BASE_URL ?>view/login.php" class="btn btn-success">
                                Proceed to Login &rarr;
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">Please fix the following:</h6>
                            <ul class="mb-0 small">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!$success): ?>
                        <form method="POST" id="signupForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="firstName" class="form-control" required placeholder="Ahmed"
                                        value="<?= htmlspecialchars($firstName ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="lastName" class="form-control" required placeholder="Mohammed"
                                        value="<?= htmlspecialchars($lastName ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control" required minlength="3"
                                        pattern="[a-zA-Z0-9_]+" title="Letters, numbers, and underscores only"
                                        placeholder="Ahmed785" value="<?= htmlspecialchars($username ?? '') ?>">
                                    <div class="form-text">At least 3 characters (letters, numbers, underscores only)</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" required
                                        placeholder="yourname@stu.uob.edu.bh"
                                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                    <small class="text-muted">Only @stu.uob.edu.bh or @uob.edu.bh emails are
                                        accepted.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phoneNumber" class="form-control" placeholder="+97333333333"
                                        value="<?= htmlspecialchars($phoneNumber ?? '') ?>">
                                </div>

                                <div class="col-12">
                                    <hr class="my-2">
                                    <h6 class="text-muted">🔐 Set Your Password</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" id="password" class="form-control" required
                                        placeholder="••••••••">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="confirm_password" id="confirm_password"
                                        class="form-control" required placeholder="••••••••">
                                    <div id="password-match" class="form-text"></div>
                                </div>
                                <div class="col-12">
                                    <div class="password-requirements bg-light p-3 rounded border">
                                        <strong class="d-block mb-2">Password Requirements:</strong>
                                        <div class="row">
                                            <div class="col-6">
                                                <div id="req-length" class="requirement">Minimum 6 characters</div>
                                                <div id="req-upper" class="requirement">One uppercase letter (A-Z)</div>
                                            </div>
                                            <div class="col-6">
                                                <div id="req-lower" class="requirement">One lowercase letter (a-z)</div>
                                                <div id="req-number" class="requirement">One number (0-9)</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                        Create My Account
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light text-center py-3">
                    Already have an account?
                    <a href="<?= BASE_URL ?>view/login.php" class="fw-semibold">Login here</a>
                </div>
            </div>

            <div class="text-center mt-3">
                <small class="text-white-50">
                    By signing up, you agree to the library's terms and policies.
                </small>
            </div>
        </div>
    </div>

    <script>
        // Real-time password validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordMatch = document.getElementById('password-match');
        const requirements = {
            length: document.getElementById('req-length'),
            upper: document.getElementById('req-upper'),
            lower: document.getElementById('req-lower'),
            number: document.getElementById('req-number')
        };

        function checkPassword() {
            const val = password.value;

            // Check length
            requirements.length.classList.toggle('valid', val.length >= 6);

            // Check uppercase
            requirements.upper.classList.toggle('valid', /[A-Z]/.test(val));

            // Check lowercase
            requirements.lower.classList.toggle('valid', /[a-z]/.test(val));

            // Check number
            requirements.number.classList.toggle('valid', /[0-9]/.test(val));
        }

        function checkPasswordMatch() {
            if (confirmPassword.value === '') {
                passwordMatch.innerHTML = '';
                confirmPassword.classList.remove('is-valid', 'is-invalid');
            } else if (password.value === confirmPassword.value) {
                passwordMatch.innerHTML = '<span class="text-success">✓ Passwords match</span>';
                confirmPassword.classList.remove('is-invalid');
                confirmPassword.classList.add('is-valid');
            } else {
                "text-danger" > ✗Passwords do not match < /span>';
                passwordMatch.innerHTML = '<span class="text-danger">✗ Passwords do not match</span>';
                ord.classList.remove('is-valid');
                confirmPassword.classList.add('is-invalid');
            }
        }
        }
        }

        password.addEventListener('input', function() {
                    password.addEventListener('input', function() {
                        checkPassword();
                        if (confirmPassword.value) checkPasswordMatch();
                    });

                    confirmPassword.addEventListener('input', checkPasswordMatch);
                    confirmPassword.addEventListener('input', checkPasswordMatch);

                    // Form validation before submitubmit
                    document.getElementById('signupForm')?.addEventListener('submit', function(e) {
                            const pwd = password.value;
                            value;
                            const confirm = confirmPassword.value;
                            rm = confirmPassword.value;

                            if (pwd.length < 6 || !/[A-Z]/.test(pwd) || !/[a-z]/.test(pwd) || !/[0-9]/.test(pwd)) {
                                if (pwd.length < 6 || !/[A-Z]/.test(pwd) || !/[a-z]/.test(pwd) || !/[0-9]/.test(pwd)) {
                                    e.preventDefault();;
                                    alert(
                                        'Password does not meet all requirements. Please check the password requirements below.'
                                    );
                                    s not meet all requirements.Please check the password requirements below.
                                    ');
                                    password.focus();
                                    return;
                                }

                                if (pwd !== confirm) {
                                    if (pwd !== confirm) {
                                        e.preventDefault();
                                        e.preventDefault();
                                        alert('Passwords do not match. Please re-enter your password.');
                                        alert('Passwords do not match. Please re-enter your password.');
                                        confirmPassword.focus();
                                        return;
                                        return;
                                    }
                                }
                            });
                    });






                <
                /html></body > < script src =
                "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" >
    </script>
    </script>
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>