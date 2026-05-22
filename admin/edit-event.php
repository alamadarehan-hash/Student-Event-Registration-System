<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) { header("Location: events.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $slots = intval($_POST['slots'] ?? 0);

    if (!empty($title) && !empty($event_date) && $slots >= 0) {
        $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, slots = ? WHERE id = ?")->execute([$title, $description, $event_date, $slots, $id]);
        header("Location: events.php");
        exit;
    }
}
include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container mt-5" style="max-width: 600px;">
    <div class="card sys-card p-4">
        <h3 class="fw-bold text-dark mb-4">Edit Event</h3>
        <form action="edit-event.php?id=<?= $id ?>" method="POST">
            <div class="mb-3">
                <label class="form-label small">Event Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($event['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label small">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($event['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label small">Event Date</label>
                <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($event['event_date']) ?>" required>
            </div>
            <div class="mb-4">
                <label class="form-label small">Available Slots</label>
                <input type="number" name="slots" class="form-control" value="<?= htmlspecialchars($event['slots']) ?>" min="0" required>
            </div>
            <button type="submit" class="btn btn-sys-primary w-100">Update Event Settings</button>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>