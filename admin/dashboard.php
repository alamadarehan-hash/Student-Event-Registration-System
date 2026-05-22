<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* FETCH EVENTS */
$stmt = $pdo->query("
    SELECT * FROM events 
    ORDER BY event_date DESC
");
$events = $stmt->fetchAll();

include '../includes/header.php';
?>

<style>
.card-link {
    text-decoration: none; /* Removes default hyperlink underline */
    color: inherit;        /* Keeps your original text colors */
    display: block;
}

.admin-sys-card {
    transition: transform 0.5s ease, box-shadow 0.5s ease, border-color 0.5s ease;
    overflow: hidden;
    cursor: pointer;       /* Makes it obvious the card is clickable */
}

/* Smooth physics on hover */
.admin-sys-card:hover {
    transform: translateY(-7px);
    box-shadow: 0px 10px 15px rgba(0, 0, 0, 0.15) !important;
    border-color: #3b82f6; 
}
</style>

<div class="mb-4 pt-2">
    <h2 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">
        Dashboard
    </h2>

    <p class="text-secondary small mb-0">
        Welcome to the Student Event Management Dashboard
    </p>
</div>

<div class="row g-3">

    <?php if ($events): ?>
        <?php foreach ($events as $event): ?>

            <div class="col-12 col-sm-6 col-md-3">
                
                <a href="event-details.php?id=<?= $event['id'] ?>" class="card-link">

                    <div class="card admin-sys-card h-100 shadow-sm">

                        <?php if (!empty($event['thumbnail'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($event['thumbnail']) ?>"
                                 class="card-img-top"
                                 style="height:160px;object-fit:cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                 style="height:160px;">
                                <span class="text-muted small">No Image</span>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">

                            <h6 class="fw-bold text-dark mb-1">
                                <?= htmlspecialchars($event['title']) ?>
                            </h6>

                            <div class="text-muted small mb-1">
                                <?= date('M d, Y h:i A', strtotime($event['event_date'])) ?>
                            </div>

                            <div class="text-secondary small mb-2">
                                📍 <?= htmlspecialchars($event['location'] ?: 'N/A') ?>
                            </div>

                            <div class="fw-semibold small">
                                Slots: <?= $event['slots'] ?>
                            </div>

                        </div>

                    </div>
                </a>

            </div>

        <?php endforeach; ?>
    <?php else: ?>

        <div class="col-12 text-center text-muted py-4">
            No events available
        </div>

    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>