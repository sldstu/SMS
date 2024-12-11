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
$query = $conn->prepare("SELECT event_id, event_name, event_description, event_date, event_time, event_location, event_image FROM events");
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
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            cursor: pointer;
        }

        .event-card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.7); /* Green glow effect on hover */
        }

        .event-name-overlay {
            position: absolute;
            bottom: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            text-align: center;
            padding: 15px;
            border-radius: 0 0 10px 10px;
        }

        .event-description {
            font-size: 0.9em;
            margin-top: 5px;
            text-align: center;
            color: white;
            max-height: 40px; /* Limit height to avoid overflow */
            overflow: hidden;
            text-overflow: ellipsis; /* Adds ellipsis if the text is too long */
            white-space: nowrap;
        }

        .modal-content {
            border-radius: 15px;
        }

        #event-image {
            max-height: 350px;
            width: 100%;
            object-fit: cover;
            border-radius: 10px;
        }

        .modal-body p {
            font-size: 1.1em;
        }

        .modal-header h5 {
            font-size: 1.5em;
            font-weight: bold;
        }

        /* Modal Content Styling to Avoid Description Overflow */
        .modal-body {
            max-height: 70vh; /* Set a maximum height for the modal */
            overflow-y: auto; /* Enable vertical scrolling if content overflows */
        }

        #event-description {
            white-space: normal; /* Allow multiline description */
            word-wrap: break-word; /* Allow text to break and wrap in the container */
        }
    </style>
</head>

<body class="bg-light">

    <div class="container my-5">
        <h1 class="text-center text-maroon">Upcoming Events</h1>
        <br>
        <div class="row">
            <?php foreach ($events as $event): ?>
                <div class="col-md-4 mb-4">
                    <div class="event-card shadow-lg" style="background-image: url('data:image/jpeg;base64,<?= $event['event_image'] ?>');" onclick="showEventDetails(<?= $event['event_id'] ?>)">
                        <div class="event-name-overlay">
                            <h5><?= htmlspecialchars($event['event_name']) ?></h5>
                            <p class="event-description"><?= htmlspecialchars($event['event_description']) ?></p>
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
                    <!-- Event Image -->
                    <img id="event-image" alt="Event Image">
                    <br><br>

                    <!-- Event Details -->
                    <div class="mb-3">
                        <p><strong>Description:</strong> <span id="event-description"></span></p>
                        <p><strong>Time:</strong> <span id="event-time"></span></p>
                        <p><strong>Location:</strong> <span id="event-location"></span></p>
                        <p><strong>Date:</strong> <span id="event-date"></span></p>
                    </div>

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
                // Set modal content
                document.getElementById('event-name').innerText = event.event_name;
                document.getElementById('event-time').innerText = event.event_time;
                document.getElementById('event-location').innerText = event.event_location;
                document.getElementById('event-date').innerText = event.event_date;
                document.getElementById('event-description').innerText = event.event_description;

                // Set the event image in the modal
                document.getElementById('event-image').src = 'data:image/jpeg;base64,' + event.event_image;

                // Show modal
                new bootstrap.Modal(document.getElementById('eventDetailsModal')).show();
            }
        }
    </script>

</body>

</html>
