<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['toast'])) {
    $_SESSION['toast'] = null;
}

/* FETCH USER */
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

/* UPDATE PROFILE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    $profilePic = $user['profile_pic'];

    /* PROFILE IMAGE */
    if (!empty($_FILES['profile_pic']['name'])) {

        $uploadDir = "../uploads/profile/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $profilePic = time() . "_" . basename($_FILES["profile_pic"]["name"]);

        move_uploaded_file(
            $_FILES["profile_pic"]["tmp_name"],
            $uploadDir . $profilePic
        );
    }

    $updatePassword = false;

    /* PASSWORD VALIDATION */
    if (!empty($new_password) || !empty($old_password)) {

        if (empty($old_password)) {

            $_SESSION['toast'] = ['type'=>'danger','message'=>'Old password is required'];

        } elseif (!password_verify($old_password, $user['password'])) {

            $_SESSION['toast'] = ['type'=>'danger','message'=>'Old password is incorrect'];

        } elseif ($new_password !== $confirm_password) {

            $_SESSION['toast'] = ['type'=>'danger','message'=>'Passwords do not match'];

        } elseif (strlen($new_password) < 6) {

            $_SESSION['toast'] = ['type'=>'danger','message'=>'Password must be at least 6 characters'];

        } else {
            $updatePassword = true;
        }
    }

    /* UPDATE DATABASE */
    if (!isset($_SESSION['toast']) || $_SESSION['toast'] === null) {

        if ($updatePassword) {

            $hashed = password_hash($new_password, PASSWORD_DEFAULT);

            $upd = $pdo->prepare("
                UPDATE users 
                SET name = ?, password = ?, profile_pic = ?
                WHERE id = ?
            ");

            $upd->execute([$name, $hashed, $profilePic, $_SESSION['user_id']]);

        } else {

            $upd = $pdo->prepare("
                UPDATE users 
                SET name = ?, profile_pic = ?
                WHERE id = ?
            ");

            $upd->execute([$name, $profilePic, $_SESSION['user_id']]);
        }

        $_SESSION['user_name'] = $name;

        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Profile updated successfully!'
        ];
    }
}

include '../includes/header.php';
?>

<div class="container py-4">

    <div class="card border-0 shadow-sm rounded-4 mx-auto" style="max-width:600px;">
        <div class="card-body p-4">

            <!-- PROFILE -->
            <div class="text-center mb-4">

                <?php if (!empty($user['profile_pic'])): ?>
                    <img src="../uploads/profile/<?= htmlspecialchars($user['profile_pic']) ?>"
                         style="width:110px;height:110px;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center mx-auto"
                         style="width:110px;height:110px;border-radius:50%;font-size:2rem;font-weight:bold;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>

                <h3 class="fw-bold mt-3">
                    <?= htmlspecialchars($user['name']) ?>
                </h3>

                <p class="text-muted small">Manage your account information</p>

            </div>

            <!-- FORM -->
            <form method="POST" enctype="multipart/form-data"
                  onsubmit="return confirm('Are you sure you want to update your profile?')">

                <div class="mb-3">
                    <label class="form-label small">Full Name</label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Email</label>
                    <input type="email" class="form-control"
                           value="<?= htmlspecialchars($user['email']) ?>" disabled>
                </div>

                <!-- OLD PASSWORD -->
                <div class="mb-3">
                    <label class="form-label small">Old Password</label>
                    <div class="input-group">
                        <input type="password" id="old_password" name="old_password" class="form-control">
                        <button type="button" class="btn btn-outline-secondary toggle-pass"
                                data-target="old_password">Show</button>
                    </div>
                </div>

                <!-- NEW PASSWORD -->
                <div class="mb-3">
                    <label class="form-label small">New Password</label>
                    <div class="input-group">
                        <input type="password" id="new_password" name="new_password" class="form-control">
                        <button type="button" class="btn btn-outline-secondary toggle-pass"
                                data-target="new_password">Show</button>
                    </div>
                </div>

                <!-- CONFIRM PASSWORD -->
                <div class="mb-3">
                    <label class="form-label small">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        <button type="button" class="btn btn-outline-secondary toggle-pass"
                                data-target="confirm_password">Show</button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small">Profile Picture</label>
                    <input type="file" name="profile_pic" class="form-control" accept="image/*">
                </div>

                <button class="btn btn-primary w-100 rounded-pill">
                    Save Changes
                </button>

            </form>

        </div>
    </div>

</div>

<!-- TOAST -->
<?php if (!empty($_SESSION['toast'])): ?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">

    <div id="liveToast"
         class="toast align-items-center text-bg-<?= $_SESSION['toast']['type'] ?> border-0">

        <div class="d-flex">
            <div class="toast-body">
                <?= $_SESSION['toast']['message'] ?>
            </div>

            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"></button>
        </div>

    </div>
</div>

<?php unset($_SESSION['toast']); ?>
<?php endif; ?>

<!-- JS FIX -->
<script>
document.addEventListener("DOMContentLoaded", function () {

    // TOAST
    const toastEl = document.getElementById('liveToast');
    if (toastEl) {
        new bootstrap.Toast(toastEl).show();
    }

    // SHOW / HIDE PASSWORD (FIXED)
    document.querySelectorAll(".toggle-pass").forEach(btn => {

        btn.addEventListener("click", function () {

            const target = this.getAttribute("data-target");
            const input = document.getElementById(target);

            if (!input) return;

            if (input.type === "password") {
                input.type = "text";
                this.textContent = "Hide";
            } else {
                input.type = "password";
                this.textContent = "Show";
            }
        });

    });

});
</script>

<?php include '../includes/footer.php'; ?>