<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$message = ''; 
$msg_type = '';

/* =========================
   REGISTER
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reg_event_id'])) {

    $ev_id = intval($_POST['reg_event_id']);
    $u_id = $_SESSION['user_id'];

    $chk = $pdo->prepare("SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
    $chk->execute([$u_id, $ev_id]);

    if ($chk->fetch()) {
        $message = "You are already registered for this event.";
        $msg_type = "warning";

    } else {

        $evObj = $pdo->prepare("SELECT slots FROM events WHERE id = ?");
        $evObj->execute([$ev_id]);
        $event = $evObj->fetch();

        if ($event && $event['slots'] > 0) {

            $pdo->beginTransaction();

            try {
                $pdo->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)")
                    ->execute([$u_id, $ev_id]);

                $pdo->prepare("UPDATE events SET slots = slots - 1 WHERE id = ?")
                    ->execute([$ev_id]);

                $pdo->commit();

                $message = "Registration successful!";
                $msg_type = "success";

            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Database error.";
                $msg_type = "danger";
            }

        } else {
            $message = "No available slots left.";
            $msg_type = "danger";
        }
    }
}

/* =========================
   CANCEL
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_event_id'])) {

    $ev_id = intval($_POST['cancel_event_id']);
    $u_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        $pdo->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?")
            ->execute([$u_id, $ev_id]);

        $pdo->prepare("UPDATE events SET slots = slots + 1 WHERE id = ?")
            ->execute([$ev_id]);

        $pdo->commit();

        $message = "Registration cancelled successfully.";
        $msg_type = "warning";

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Failed to cancel registration.";
        $msg_type = "danger";
    }
}

/* =========================
   FETCH EVENTS (ONLY UPCOMING)
========================= */
$stmt = $pdo->prepare("
    SELECT 
        e.*,
        CASE 
            WHEN r.id IS NULL THEN 0 
            ELSE 1 
        END AS registration_status
    FROM events e
    LEFT JOIN registrations r 
        ON e.id = r.event_id 
        AND r.user_id = ?

    WHERE e.event_date >= NOW()

    GROUP BY e.id
    ORDER BY e.event_date ASC
");

$stmt->execute([$_SESSION['user_id']]);
$events = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
/* ==========================================================================
   UIVERSE-INSPIRED HOVER STYLE INTEGRATION
   ========================================================================== */
.event-card-custom {
    background-color: #fff; /* NOTE: Switch to #24233b if you prefer a dark theme card background */
    border: 1px solid #e2e8f0;
    border-radius: 8px;     /* Uiverse radius setting */
    z-index: 1;             /* Uiverse layout depth setting */
    box-shadow: 0px 10px 10px rgba(73, 70, 92, 0.12); /* Balanced for light viewports */
    transition: 0.5s;       /* Uiverse signature transition speed */
    overflow: hidden;
}

/* Hover configuration applied smoothly to your records management list items */
.event-card-custom:hover {
    transform: translateY(-7px);     /* Uiverse rise physics */
    box-shadow: 0px 10px 15px black; /* Uiverse shadow transition definition */
    border-color: #3b82f6;          /* Clean emphasis ring on focal element */
}

/* Clean Padding and Formatting for inside elements */
.event-body-custom {
    padding: 20px;
}
.event-category-banner {
    padding: 6px 20px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    background-color: #f8fafc;
    border-bottom: 1px solid #edf2f7;
    color: #64748b;
}
.meta-info-item {
    font-size: 0.875rem;
    color: #4a5568;
    margin-bottom: 6px;
}
.meta-info-item i {
    margin-right: 8px;
    color: #3b82f6;
}
</style>

<?php if (!empty($message)): ?>
<div class="alert alert-<?= $msg_type ?> alert-dismissible fade show rounded-3 shadow-sm mb-4">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">

<?php if (count($events) > 0): ?>
    <?php foreach ($events as $event): ?>

        <div class="col-xl-4 col-md-6 mb-4 event-search-item">

            <div class="event-card-custom">

                <?php if (!empty($event['thumbnail'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($event['thumbnail']) ?>"
                         style="width:100%;height:180px;object-fit:cover;border-radius:8px 8px 0 0;">
                <?php else: ?>
                    <div style="height:180px;background:#f1f1f1;border-radius:8px 8px 0 0;"
                         class="d-flex align-items-center justify-content-center text-muted">
                        No Image
                    </div>
                <?php endif; ?>

                <div class="event-category-banner">
                    <?= htmlspecialchars($event['category'] ?? 'EVENT') ?>
                </div>

                <div class="event-body-custom">

                    <h4 class="event-card-title fw-bold mb-3">
                        <?= htmlspecialchars($event['title']) ?>
                    </h4>

                    <div class="meta-info-item">
                        <i class="bi bi-calendar3"></i>
                        <span>
                            <?= date('M d, Y', strtotime($event['event_date'])) ?> • 1:00 PM
                        </span>
                    </div>

                    <div class="meta-info-item mb-3">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>
                            <?= htmlspecialchars($event['location'] ?? 'Audio Visual Room') ?>
                        </span>
                    </div>

                    <p class="text-secondary small mb-4">
                        <?= htmlspecialchars($event['description'] ?: 'No description available.') ?>
                    </p>

                    <div class="pt-2 border-top">

                        <div class="d-flex justify-content-between mb-3">
                            <span class="small fw-semibold text-dark">
                                Slots Left: <span class="text-primary fw-bold"><?= $event['slots'] ?></span>
                            </span>
                        </div>

                        <?php if ($event['registration_status'] == 1): ?>

                            <button class="btn btn-danger w-100"
                                    onclick="openCancelModal(<?= $event['id'] ?>, '<?= htmlspecialchars($event['title']) ?>')">
                                Cancel Registration
                            </button>

                            <form id="cancel-form-<?= $event['id'] ?>" method="POST" style="display:none;">
                                <input type="hidden" name="cancel_event_id" value="<?= $event['id'] ?>">
                            </form>

                        <?php else: ?>

                            <button class="btn btn-primary w-100"
                                    onclick="openRegisterModal(<?= $event['id'] ?>, '<?= htmlspecialchars($event['title']) ?>')">
                                Register
                            </button>

                            <form id="form-<?= $event['id'] ?>" method="POST" style="display:none;">
                                <input type="hidden" name="reg_event_id" value="<?= $event['id'] ?>">
                            </form>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    <?php endforeach; ?>
<?php else: ?>

    <div class="col-12 text-center text-muted py-5">
        No upcoming events available.
    </div>

<?php endif; ?>

</div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p id="confirmText" class="mb-0 text-secondary"></p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button class="btn btn-primary" id="confirmYesBtn">Yes</button>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let targetFormId = null;
const confirmModalEl = new bootstrap.Modal(document.getElementById('confirmModal'));
const confirmText = document.getElementById('confirmText');
const confirmYesBtn = document.getElementById('confirmYesBtn');

function openRegisterModal(eventId, eventTitle) {
    targetFormId = 'form-' + eventId;
    confirmText.textContent = `Are you sure you want to register for "${eventTitle}"?`;
    confirmModalEl.show();
}

function openCancelModal(eventId, eventTitle) {
    targetFormId = 'cancel-form-' + eventId;
    confirmText.textContent = `Are you sure you want to cancel your registration for "${eventTitle}"?`;
    confirmModalEl.show();
}

confirmYesBtn.addEventListener('click', function() {
    if (targetFormId) {
        document.getElementById(targetFormId).submit();
    }
});
</script>