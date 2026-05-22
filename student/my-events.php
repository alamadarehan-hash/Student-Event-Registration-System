<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        r.id as reg_id,
        e.title,
        e.event_date,
        e.location,
        e.thumbnail,
        e.description
    FROM registrations r 
    JOIN events e ON r.event_id = e.id 
    WHERE r.user_id = ? 
    ORDER BY e.event_date ASC
");

$stmt->execute([$_SESSION['user_id']]);
$my_events = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- SEARCH + HEADER -->
<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <!-- LEFT TITLE -->
        <div>
            <h2 class="fw-bold text-dark">Student Dashboard</h2>
            <p class="text-secondary mb-0">My Registered Events</p>
        </div>

        <!-- LEFT SEARCH -->
        <div style="max-width: 300px; width: 100%;">
            <div class="input-group shadow-sm border rounded-3 bg-white">
                <input type="text"
                       id="searchMyEvents"
                       class="form-control border-0 py-2 ps-3 small"
                       placeholder="Search my events...">

                <span class="input-group-text bg-transparent border-0 pe-3 text-secondary">
                    <i class="bi bi-search"></i>
                </span>
            </div>
        </div>

    </div>

    <div class="row">

        <?php if (count($my_events) > 0): ?>

            <?php foreach ($my_events as $row): ?>

                <div class="col-xl-4 col-md-6 mb-4 my-event-item">

                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">

                        <!-- THUMBNAIL -->
                        <?php if (!empty($row['thumbnail'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($row['thumbnail']) ?>"
                                 style="height:180px;object-fit:cover;width:100%;">
                        <?php else: ?>
                            <div style="height:180px;background:#f1f1f1;"
                                 class="d-flex align-items-center justify-content-center text-muted">
                                No Image
                            </div>
                        <?php endif; ?>

                        <div class="card-body">

                            <h5 class="fw-bold text-dark mb-1">
                                <?= htmlspecialchars($row['title']) ?>
                            </h5>

                            <div class="text-muted small mb-2">
                                <i class="bi bi-calendar3"></i>
                                <?= date('M d, Y h:i A', strtotime($row['event_date'])) ?>
                            </div>

                            <div class="text-secondary small mb-2">
                                <i class="bi bi-geo-alt-fill"></i>
                                <?= htmlspecialchars($row['location'] ?? 'No location') ?>
                            </div>

                            <p class="text-muted small">
                                <?= htmlspecialchars($row['description'] ?? 'No description available.') ?>
                            </p>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="col-12">
                <div class="alert alert-light text-center text-muted py-5">
                    You haven't registered for any events yet.
                </div>
            </div>

        <?php endif; ?>

    </div>

</div>

<!-- SEARCH SCRIPT -->
<script>
document.getElementById('searchMyEvents')?.addEventListener('input', function () {
    let value = this.value.toLowerCase();

    document.querySelectorAll('.my-event-item').forEach(card => {
        let text = card.textContent.toLowerCase();
        card.style.display = text.includes(value) ? '' : 'none';
    });
});
</script>

<?php include '../includes/footer.php'; ?>