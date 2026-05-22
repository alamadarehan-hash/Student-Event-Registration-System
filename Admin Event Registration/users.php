<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY role ASC, name ASC");
$users = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="mb-4 pt-2">
    <h2 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">User Configuration</h2>
    <p class="text-secondary small mb-0">System user access settings control portal profiles.</p>
</div>

<div class="card admin-sys-card overflow-hidden">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width: 100px;">User ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th style="width: 150px;">Portal Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="text-muted fw-semibold">#<?= $user['id'] ?></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($user['name']) ?></td>
                        <td class="text-secondary small"><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge bg-dark px-2.5 py-1.5 rounded text-uppercase" style="font-size:0.7rem; font-weight:700;">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-secondary px-2.5 py-1.5 rounded text-uppercase" style="font-size:0.7rem; font-weight:700;">Student</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>