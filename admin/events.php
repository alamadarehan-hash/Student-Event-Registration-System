<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = "";

/* ADD / DELETE ACTIONS */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    /* ADD EVENT */
    if ($_POST['action'] === 'add') {

        $title = trim($_POST['title']);
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $event_date = $_POST['event_date'];
        $location = trim($_POST['location']);
        $slots = intval($_POST['slots']);

        $thumbnail = null;

        if (!empty($_FILES['thumbnail']['name'])) {
            $uploadDir = "../uploads/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $thumbnail = time() . "_" . basename($_FILES["thumbnail"]["name"]);
            move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $uploadDir . $thumbnail);
        }

        $ins = $pdo->prepare("
            INSERT INTO events 
            (title, category, description, event_date, location, slots, thumbnail) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $ins->execute([$title, $category, $description, $event_date, $location, $slots, $thumbnail]);

        $message = "Event successfully added.";
    }

    /* DELETE EVENT (SAFE) */
    if ($_POST['action'] === 'delete') {

        $id = intval($_POST['event_id']);

        try {
            $pdo->beginTransaction();

            $pdo->prepare("DELETE FROM registrations WHERE event_id = ?")
                ->execute([$id]);

            $pdo->prepare("DELETE FROM events WHERE id = ?")
                ->execute([$id]);

            $pdo->commit();

            $message = "Event deleted successfully.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Failed to delete event.";
        }
    }
}

/* FETCH EVENTS */
$stmt = $pdo->query("
    SELECT e.*, COUNT(r.id) as registered_count
    FROM events e
    LEFT JOIN registrations r ON e.id = r.event_id
    GROUP BY e.id
    ORDER BY e.event_date DESC
");

$events = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- STYLE -->
<style>
/* highlight old events */
.old-event {
    opacity: 0.5;
}
</style>

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4 pt-2">
    <div>
        <h2 class="fw-bold text-dark mb-1">Manage Events</h2>
        <p class="text-secondary small mb-0">Add, edit or delete events.</p>
    </div>

    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEventModal">
        <i class="bi bi-plus-lg"></i> Add New Event
    </button>
</div>

<!-- MESSAGE -->
<?php if (!empty($message)): ?>
<div class="alert alert-success">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- TABLE -->
<div class="card admin-sys-card overflow-hidden">
    <div class="table-responsive">

        <table class="table mb-0 align-middle">

            <thead>
                <tr>
                    <th>Thumbnail</th>
                    <th>Event</th>
                    <th>Date & Time</th>
                    <th>Location</th>
                    <th>Slots</th>
                    <th>Registered</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($events as $event): 
                    $isPast = strtotime($event['event_date']) < time();
                ?>

                <tr class="<?= $isPast ? 'old-event' : '' ?>">

                    <td>
                        <?php if (!empty($event['thumbnail'])): ?>
                            <img src="../uploads/<?= $event['thumbnail'] ?>"
                                 style="width:50px;height:50px;object-fit:cover;border-radius:6px;">
                        <?php else: ?>
                            <span class="text-muted small">No Image</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($event['title']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($event['category']) ?></small>
                    </td>

                    <td>
                        <?= date('M d, Y h:i A', strtotime($event['event_date'])) ?>
                    </td>

                    <td class="text-secondary small">
                        <?= htmlspecialchars($event['location'] ?: 'Audio Visual Room') ?>
                    </td>

                    <td><?= $event['slots'] ?></td>
                    <td><?= $event['registered_count'] ?></td>

                    <td>
                        <button class="btn btn-sm btn-danger"
                                onclick="openDeleteModal(<?= $event['id'] ?>, '<?= htmlspecialchars($event['title']) ?>')">
                            Delete
                        </button>
                    </td>

                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <div class="modal-body">

                    <input type="text" name="title" class="form-control mb-2" placeholder="Event Title" required>
                    <input type="text" name="category" class="form-control mb-2" placeholder="Category" required>
                    <textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>

                    <!-- DATE WITH SAVE BUTTON -->
                    <div class="input-group mb-2">
                        <input type="datetime-local"
                               name="event_date"
                               id="event_date"
                               class="form-control"
                               required>
                        <button type="button" class="btn btn-success" onclick="saveDateTime()">
                            Save
                        </button>
                    </div>

                    <input type="number" name="slots" class="form-control mb-2" value="50">
                    <input type="text" name="location" class="form-control mb-2" placeholder="Location">
                    <input type="file" name="thumbnail" class="form-control">

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Save Event</button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p id="deleteText"></p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>

        </div>
    </div>
</div>

<script>
let deleteId = null;

/* DISABLE PAST DATE */
window.addEventListener("load", function () {
    let input = document.getElementById("event_date");

    let now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

    input.min = now.toISOString().slice(0,16);
});

function saveDateTime() {
    let input = document.getElementById("event_date");

    if (!input.value) {
        input.focus();
        return;
    }

    input.classList.add("is-valid");
    setTimeout(() => input.classList.remove("is-valid"), 1000);
}

/* DELETE MODAL */
function openDeleteModal(id, title) {
    deleteId = id;
    document.getElementById("deleteText").innerText =
        "Delete event: " + title + " ?";
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

document.getElementById("confirmDeleteBtn").onclick = function () {

    let form = document.createElement("form");
    form.method = "POST";

    form.innerHTML = `
        <input name="action" value="delete">
        <input name="event_id" value="${deleteId}">
    `;

    document.body.appendChild(form);
    form.submit();
};
</script>

<?php include '../includes/footer.php'; ?>