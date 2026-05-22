<?php
session_start();
require_once 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['user_role'] === 'admin' ? 'admin/events.php' : 'student/dashboard.php'));
    exit;
}

$error = '';

/* LOGIN PROCESS */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            /* REMEMBER ME */
            if (isset($_POST['remember'])) {
                setcookie("remember_email", $email, time() + (86400 * 7), "/"); // 7 days
            } else {
                setcookie("remember_email", "", time() - 3600, "/");
            }

            header("Location: " . ($user['role'] === 'admin' ? 'admin/events.php' : 'student/dashboard.php'));
            exit;

        } else {
            $error = "Invalid combination of email/username or password.";
        }

    } else {
        $error = "Please fill out all parameters.";
    }
}

include 'includes/header.php';
?>

<!-- LOGIN UI -->
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card sys-card p-4 my-5" style="width: 100%; max-width: 460px;">

        <div class="text-center mb-4">
            <div class="sys-icon-banner mb-3">
                <i class="bi bi-bank2"></i>
            </div>

            <h2 class="fw-bold text-dark mb-1" style="font-size: 1.75rem;">
                Student Event Registration System
            </h2>

            <p class="text-secondary small">
                Welcome back! Please login to continue.
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger small py-2 rounded-3">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">

            <!-- EMAIL -->
            <div class="mb-3">
                <label class="form-label small">Email or Username</label>
                <input type="text"
                       name="email"
                       value="<?= isset($_COOKIE['remember_email']) ? htmlspecialchars($_COOKIE['remember_email']) : '' ?>"
                       class="form-control"
                       placeholder="Enter your email or username"
                       required>
            </div>

            <!-- PASSWORD -->
            <div class="mb-3">
                <label class="form-label small">Password</label>

                <div class="input-group">
                    <input type="password"
                           name="password"
                           id="loginPassword"
                           class="form-control"
                           placeholder="Enter your password"
                           required>

                    <span class="input-group-text"
                          onclick="togglePasswordVisibility('loginPassword', 'loginEyeIcon')">
                        <i class="bi bi-eye" id="loginEyeIcon"></i>
                    </span>
                </div>
            </div>

            <!-- REMEMBER ME (FIXED) -->
            <div class="mb-4 form-check d-flex align-items-center">
                <input type="checkbox"
                       name="remember"
                       class="form-check-input me-2"
                       id="rememberMe"
                       <?= isset($_COOKIE['remember_email']) ? 'checked' : '' ?>>

                <label class="form-check-label small text-secondary" for="rememberMe">
                    Remember me
                </label>
            </div>

            <!-- BUTTON -->
            <button type="submit" class="btn btn-sys-primary w-100 mb-3">
                Login
            </button>

            <div class="text-center">
                <p class="small text-secondary mb-0">
                    Don't have an account?
                    <a href="register.php" class="text-primary fw-semibold text-decoration-none">
                        Register here
                    </a>
                </p>
            </div>

        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>