<?php
require_once '../MAIN/includes/clean_function.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

// Fetch all events
$query = $conn->prepare("SELECT event_id, event_name, event_start_date, event_end_date, event_location, event_image FROM events");
$query->execute();
$events = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <style>
        .event-card {
            position: relative;
            background-size: cover;
            background-position: center;
            height: 250px;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }
        .event-card:hover {
            transform: scale(1.05);
        }
        .event-name-overlay {
            position: absolute;
            bottom: 0;
            width: 100%;
            background-color: #800000;
            color: white;
            text-align: center;
            padding: 10px;
        }
        .modal-content {
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container my-5">
    <h1 class="text-center text-maroon">Upcoming Events</h1>
    <div class="row">
        <?php foreach ($events as $event): ?>
            <div class="col-md-4 mb-4">
                <div class="event-card shadow-sm" style="background-image: url('data:image/jpeg;base64,<?= $event['event_image'] ?>');" onclick="showEventDetails(<?= $event['event_id'] ?>)">
                    <div class="event-name-overlay">
                        <h5><?= htmlspecialchars($event['event_name']) ?></h5>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal for Event Details -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="event-name"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Time:</strong> <span id="event-time"></span></p>
                <p><strong>Location:</strong> <span id="event-location"></span></p>
                <p><strong>Date:</strong> <span id="event-date"></span></p>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let eventDetails = <?php echo json_encode($events); ?>;

    function showEventDetails(eventId) {
        let event = eventDetails.find(e => e.event_id == eventId);
        if (event) {
            document.getElementById('event-name').innerText = event.event_name;
            document.getElementById('event-time').innerText = event.event_end_date;
            document.getElementById('event-location').innerText = event.event_location;
            document.getElementById('event-date').innerText = event.event_start_date;

            new bootstrap.Modal(document.getElementById('eventDetailsModal')).show();
        }
    }
</script>
</body>
</html>
