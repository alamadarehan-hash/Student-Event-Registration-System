<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch registration logs
$stmt = $pdo->query("
SELECT 
    r.id,
    u.name as student_name,
    u.email as student_email,
    e.title as event_title,
    r.registered_at
FROM registrations r
JOIN users u ON r.user_id = u.id
JOIN events e ON r.event_id = e.id
ORDER BY r.registered_at DESC
");

$registrants = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- HEADER + SEARCH -->
<div class="d-flex justify-content-between align-items-center mb-4 pt-2 flex-wrap gap-3">

    <div>
        <h2 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">
            Registrants
        </h2>
        <p class="text-secondary small mb-0">
            View students registered for each event.
        </p>
    </div>

    <!-- SEARCH BOX -->
    <div style="max-width: 300px; width: 100%;">
        <div class="input-group bg-white border rounded-3 shadow-sm">
            <input type="text"
                   id="searchRegistrants"
                   class="form-control border-0 py-2 ps-3 small"
                   placeholder="Search student or event...">

            <span class="input-group-text bg-transparent border-0 pe-3 text-secondary">
                <i class="bi bi-search"></i>
            </span>
        </div>
    </div>

</div>

<!-- TABLE -->
<div class="card admin-sys-card overflow-hidden">
    <div class="table-responsive">
        <table class="table mb-0 align-middle" id="registrantsTable">

            <thead>
                <tr>
                    <th style="width: 80px;">#</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Event</th>
                    <th>Registration Date & Time</th>
                </tr>
            </thead>

            <tbody>

                <?php if ($registrants): ?>
                    <?php foreach ($registrants as $index => $reg): ?>

                        <tr class="registrant-row">

                            <td class="text-muted fw-semibold">
                                <?= $index + 1 ?>
                            </td>

                            <td class="fw-bold text-dark student-name">
                                <?= htmlspecialchars($reg['student_name']) ?>
                            </td>

                            <td class="text-secondary small">
                                <?= htmlspecialchars($reg['student_email']) ?>
                            </td>

                            <td class="event-title">
                                <span class="fw-medium text-dark">
                                    <?= htmlspecialchars($reg['event_title']) ?>
                                </span>
                            </td>

                            <td class="text-muted small">
                                <?= date('M d, Y h:i A', strtotime($reg['registered_at'])) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>
                <?php else: ?>

                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            No student registrations logged on the server.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

<!-- SEARCH SCRIPT -->
<script>
document.getElementById('searchRegistrants')?.addEventListener('input', function () {
    let value = this.value.toLowerCase().trim();

    document.querySelectorAll('.registrant-row').forEach(row => {
        let student = row.querySelector('.student-name')?.textContent.toLowerCase() || '';
        let event = row.querySelector('.event-title')?.textContent.toLowerCase() || '';

        if (student.includes(value) || event.includes(value)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>