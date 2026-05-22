<?php
session_start();
require_once 'includes/config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    if (empty($name) || empty($email) || empty($password)) { $errors[] = "All configuration fields are mandatory."; }
    if ($password !== $confirm_password) { $errors[] = "Password criteria validation values do not match."; }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) { $errors[] = "Email string already bound to another registration element."; }
        else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $ins = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $ins->execute([$name, $email, $hashed, $role]);
            $_SESSION['success'] = "Account configured! Enter validation parameters to connect.";
            header("Location: login.php");
            exit;
        }
    }
}
include 'includes/header.php';
?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card sys-card p-4 my-5" style="width: 100%; max-width: 480px;">
        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1" style="font-size: 2rem;">Create an Account</h2>
            <p class="text-secondary small">Join and start registering for events!</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger small py-2 rounded-3"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="mb-3">
                <label class="form-label small">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
            </div>
            <div class="mb-3">
                <label class="form-label small">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label class="form-label small">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="regPassword" class="form-control input-has-toggle" placeholder="Create a password" required>
                    <span class="input-group-text input-group-text-sys" onclick="togglePasswordVisibility('regPassword', 'regEyeIcon1')">
                        <i class="bi bi-eye" id="regEyeIcon1"></i>
                    </span>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small">Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control input-has-toggle" placeholder="Confirm your password" required>
                    <span class="input-group-text input-group-text-sys" onclick="togglePasswordVisibility('confirmPassword', 'regEyeIcon2')">
                        <i class="bi bi-eye" id="regEyeIcon2"></i>
                    </span>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label small">Role</label>
                <select name="role" class="form-select">
                    <option value="student">Student</option>
                </select>
            </div>
            <button type="submit" class="btn btn-sys-primary w-100 mb-3">Register</button>
            <div class="text-center">
                <p class="small text-secondary mb-0">Already have an account? <a href="login.php" class="text-primary fw-semibold text-decoration-none">Login here</a></p>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>