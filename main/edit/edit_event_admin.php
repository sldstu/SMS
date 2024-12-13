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
    $event_description = $_POST['event_description'];
    $event_start_date = $_POST['event_start_date'];
    $event_end_date = $_POST['event_end_date'];
    $event_location = $_POST['event_location'];

    // Handle image update if provided
    $queryStr = "UPDATE events SET 
                 event_name = :event_name, 
                 event_description = :event_description, 
                 event_start_date = :event_start_date, 
                 event_end_date = :event_end_date, 
                 event_location = :event_location";

    if (!empty($_FILES['event_image']['name'])) {
        $imageData = base64_encode(file_get_contents($_FILES['event_image']['tmp_name']));
        $queryStr .= ", event_image = :event_image";
    }
    
    $queryStr .= " WHERE event_id = :event_id";
    
    $query = $conn->prepare($queryStr);
    $query->bindParam(':event_name', $event_name);
    $query->bindParam(':event_description', $event_description);
    $query->bindParam(':event_start_date', $event_start_date);
    $query->bindParam(':event_end_date', $event_end_date);
    $query->bindParam(':event_location', $event_location);
    $query->bindParam(':event_id', $event_id);
    
    if (!empty($_FILES['event_image']['name'])) {
        $query->bindParam(':event_image', $imageData);
    }

    if ($query->execute()) {
        echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update event']);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    $query = $conn->prepare("SELECT * FROM events WHERE event_id = :event_id");
    $query->bindParam(':event_id', $event_id);
    $query->execute();
    $event = $query->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        ?>
        <form id="editEventForm" class="needs-validation" novalidate enctype="multipart/form-data">
            <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['event_id']) ?>">

            <div class="mb-3">
                <label for="event_name" class="form-label">Event Name</label>
                <input type="text" class="form-control" id="event_name" name="event_name" value="<?= htmlspecialchars($event['event_name']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="event_description" class="form-label">Event Description</label>
                <textarea class="form-control" id="event_description" name="event_description" rows="3" required><?= htmlspecialchars($event['event_description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="event_start_date" class="form-label">Event Start Date</label>
                <input type="date" class="form-control" id="event_start_date" name="event_start_date" value="<?= htmlspecialchars($event['event_start_date']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="event_end_date" class="form-label">Event End Date</label>
                <input type="date" class="form-control" id="event_end_date" name="event_end_date" value="<?= htmlspecialchars($event['event_end_date']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="event_location" class="form-label">Event Location</label>
                <input type="text" class="form-control" id="event_location" name="event_location" value="<?= htmlspecialchars($event['event_location']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="event_image" class="form-label">Event Image</label>
                <input type="file" class="form-control" id="event_image" name="event_image">
                <div class="text-muted">Leave empty if no image update is required.</div>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
        <script>
            document.getElementById('editEventForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch('edit_event_admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Event updated successfully!');
                        location.reload(); // Refresh the page to reflect changes
                    } else {
                        alert(data.message || 'Error updating event.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            });
        </script>
        <?php
    } else {
        echo "Event not found.";
    }
    exit();
}
?>