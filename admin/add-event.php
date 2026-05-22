<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $slots = intval($_POST['slots'] ?? 0);

    if (!empty($title) && !empty($event_date) && $slots > 0) {
        $pdo->prepare("INSERT INTO events (title, description, event_date, slots) VALUES (?, ?, ?, ?)")->execute([$title, $description, $event_date, $slots]);
        header("Location: events.php");
        exit;
    }
}
include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container mt-5" style="max-width: 600px;">
    <div class="card sys-card p-4">
        <h3 class="fw-bold text-dark mb-4">Add New Event</h3>
        <form action="add-event.php" method="POST">
            <div class="mb-3">
                <label class="form-label small">Event Title</label>
                <input type="text" name="title" class="form-control" placeholder="Enter event title" required>
            </div>
            <div class="mb-3">
                <label class="form-label small">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Enter descriptive text details context"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label small">Event Date</label>
                <input type="date" name="event_date" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label small">Available Slots</label>
                <input type="number" name="slots" class="form-control" placeholder="Enter maximum available slots" min="1" required>
            </div>
            <button type="submit" class="btn btn-sys-primary w-100">Save Event</button>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>