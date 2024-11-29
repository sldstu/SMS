<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '../../database/database.class.php';
$conn = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];

    $query = $conn->prepare("UPDATE events SET event_name = :event_name, event_date = :event_date WHERE event_id = :event_id");
    $query->bindParam(':event_name', $event_name);
    $query->bindParam(':event_date', $event_date);
    $query->bindParam(':event_id', $event_id);

    if ($query->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit();
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    $query = $conn->prepare("SELECT * FROM events WHERE event_id = :event_id");
    $query->bindParam(':event_id', $event_id);
    $query->execute();
    $event = $query->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        ?>
        <form id="editEventForm" class="needs-validation" novalidate>
            <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['event_id']) ?>">
            <div class="mb-3">
                <label for="event_name" class="form-label">Event Name</label>
                <input type="text" class="form-control" id="event_name" name="event_name" value="<?= htmlspecialchars($event['event_name']) ?>" required>
                <div class="invalid-feedback">Please enter an event name.</div>
            </div>
            <div class="mb-3">
                <label for="event_date" class="form-label">Event Date</label>
                <input type="date" class="form-control" id="event_date" name="event_date" value="<?= htmlspecialchars($event['event_date']) ?>" required>
                <div class="invalid-feedback">Please select an event date.</div>
            </div>
        </form>
        <?php
    } else {
        echo "Event not found.";
    }
    exit();
}
?>
