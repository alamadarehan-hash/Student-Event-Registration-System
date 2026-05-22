<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get event ID from URL secure it
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

/* 1. FETCH EVENT DETAILS */
$eventStmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$eventStmt->execute([$event_id]);
$event = $eventStmt->fetch();

if (!$event) {
    echo "Event not found.";
    exit;
}

/* 2. FETCH REGISTERED STUDENTS FOR THIS EVENT */
$registrantsStmt = $pdo->prepare("
    SELECT u.id, u.name, u.email, r.registered_at 
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = ?
    ORDER BY r.registered_at DESC
");
$registrantsStmt->execute([$event_id]);
$registrants = $registrantsStmt->fetchAll();

include '../includes/header.php';
?>

<div class="mb-4 pt-2">
    <a href="dashboard.php" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
    
    <h2 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">
        <?= htmlspecialchars($event['title']) ?>
    </h2>
    <p class="text-secondary small mb-0">
        📍 <?= htmlspecialchars($event['location'] ?: 'N/A') ?> | 📅 <?= date('M d, Y h:i A', strtotime($event['event_date'])) ?>
    </p>
</div>

<div class="row">
    <div class="col-12">
        <div class="card admin-sys-card p-4 shadow-sm bg-white">
            <h5 class="fw-bold text-dark mb-3">Registered Students (<?= count($registrants) ?>)</h5>
            
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Email Address</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($registrants): ?>
                            <?php foreach ($registrants as $student): ?>
                                <tr>
                                    <td>#<?= $student['id'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($student['name']) ?></td>
                                    <td><?= htmlspecialchars($student['email']) ?></td>
                                    <td class="text-muted">
                                        <?= date('M d, Y h:i A', strtotime($student['registered_at'] ?? 'now')) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No students have registered for this event yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>