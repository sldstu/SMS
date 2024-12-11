<?php
require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

// Fetch all events
$query = $conn->prepare("SELECT * FROM events");
$query->execute();
$events = $query->fetchAll(PDO::FETCH_ASSOC);

// Serve images from the database
if (isset($_GET['event_image_id'])) {
    $eventId = $_GET['event_image_id'];
    $query = $conn->prepare("SELECT event_image FROM events WHERE event_id = :event_id");
    $query->bindParam(':event_id', $eventId, PDO::PARAM_INT);
    $query->execute();
    $event = $query->fetch(PDO::FETCH_ASSOC);

    if ($event && $event['event_image']) {
        // Determine the image's MIME type
        $imageData = base64_decode($event['event_image']);
        $mimeType = 'image/jpeg'; // Adjust based on your image format (jpeg, png, gif, etc.)

        // Send the image to the browser
        header('Content-Type: ' . $mimeType);
        echo $imageData; // Output the image data
    } else {
        // If no image is found, return a default image
        header('HTTP/1.0 404 Not Found');
        echo 'Image not found';
    }
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle adding a new event
    
    if (isset($_POST['event_name'])) {
        $eventName = $_POST['event_name'];
        $description = $_POST['event_description'];
        $eventDate = $_POST['event_date'];
        $eventTime = $_POST['event_time'];
        $location = $_POST['event_location'];
        $facilitators = null;
        // $facilitators = isset($_POST['facilitators']) ? implode(',', $_POST['facilitators']) : null;

        // Handle image upload as base64
        $imageData = null;
        if (!empty($_FILES['event_image']['name'])) {
            $imageData = base64_encode(file_get_contents($_FILES['event_image']['tmp_name'])); // Convert the image to base64 string
        }

        // Insert the event into the database
        $query = $conn->prepare("INSERT INTO events (event_name, event_description, event_date, event_time, event_location, facilitator, event_image) VALUES (:event_name, :event_description, :event_date, :event_time, :event_location, :facilitator, :event_image)");
        $query->bindParam(':event_name', $eventName);
        $query->bindParam(':event_description', $description);
        $query->bindParam(':event_date', $eventDate);
        $query->bindParam(':event_time', $eventTime);
        $query->bindParam(':event_location', $location);
        $query->bindParam(':facilitator', $facilitators);
        $query->bindParam(':event_image', $imageData); // Store the base64 encoded image
        $query->execute();

        // Return the new event
        $newEventId = $conn->lastInsertId();
        $newEvent = [
            'event_id' => $newEventId,
            'event_name' => $eventName,
            'event_description' => $description,
            'event_date' => $eventDate,
            'event_time' => $eventTime,
            'event_location' => $location,
            'facilitator' => $facilitators,
            'event_image' => 'events.php?event_image_id=' . $newEventId, // Image URL
        ];
        echo json_encode(['status' => 'success', 'event' => $newEvent]);
        exit();
    } elseif (isset($_GET['event_id'])) {
        $eventId = $_GET['event_id'];
        $query = $conn->prepare("SELECT * FROM events WHERE event_id = :event_id");
        $query->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $query->execute();
        $event = $query->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            echo json_encode(['status' => 'success', 'event' => $event]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Event not found.']);
        }
        exit();
    }
}

// Ensure $events is populated
if (!$events) {
    $events = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <style>
        .card-img-top {
            height: 150px;
            object-fit: cover;
        }

        .modal-img {
            max-height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .card:active {
            transform: scale(0.98);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .card:hover {
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">Events Management</h1>

        <!-- Button to add a new event -->
        <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#addEventModal">Add Event</button>

        <div class="row">
            <?php foreach ($events as $event): ?>
                <div class="col-md-4 mb-4" id="event-card-<?= $event['event_id'] ?>" class="event-card">
                    <div class="card shadow-sm" onclick="showEventDetails(<?= $event['event_id'] ?>)">
                        <img src="data:image/png;base64,<?= $event['event_image'] ?>" class="card-img-top" alt="Event Image">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($event['event_name']) ?></h5>
                            <p class="card-text text-truncate"><?= htmlspecialchars($event['event_description'] ?? 'No description available.') ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="event-name"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img id="event-image" class="modal-img mb-3" src="" alt="Event Image">
                    <p id="event-description"></p>
                    <p><strong>Date:</strong> <span id="event-date"></span></p>
                    <p><strong>Time:</strong> <span id="event-time"></span></p>
                    <p><strong>Location:</strong> <span id="event-location"></span></p>
                    <p><strong>Facilitator:</strong> <span id="event-facilitator"></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addEventForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addEventLabel">Add Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="event_name" class="form-label">Event Name</label>
                            <input type="text" class="form-control" id="event_name" name="event_name">
                        </div>
                        <div class="mb-3">
                            <label for="event_description" class="form-label">Description</label>
                            <textarea class="form-control" id="event_description" name="event_description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="event_date" class="form-label">Event Date</label>
                            <input type="date" class="form-control" id="event_date" name="event_date">
                        </div>
                        <div class="mb-3">
                            <label for="event_time" class="form-label">Event Time</label>
                            <input type="time" class="form-control" id="event_time" name="event_time">
                        </div>
                        <div class="mb-3">
                            <label for="event_location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="event_location" name="event_location">
                        </div>
                        <div class="mb-3">
                            <label for="facilitators" class="form-label">Facilitator(s)</label>
                            <input type="text" class="form-control" id="facilitators" name="facilitators">
                        </div>
                        <div class="mb-3">
                            <label for="event_image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="event_image" name="event_image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Event</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showEventDetails(eventId) {
            fetch(`events.php?event_id=${eventId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const event = data.event;
                        document.getElementById('event-name').textContent = event.event_name;
                        document.getElementById('event-description').textContent = event.event_description;
                        document.getElementById('event-date').textContent = event.event_date;
                        document.getElementById('event-time').textContent = event.event_time;
                        document.getElementById('event-location').textContent = event.event_location;
                        document.getElementById('event-facilitator').textContent = event.facilitator;
                        document.getElementById('event-image').src = `events.php?event_image_id=${event.event_id}`;
                        new bootstrap.Modal(document.getElementById('eventDetailsModal')).show();
                    } else {
                        alert('Event not found.');
                    }
                });
        }

        document.getElementById('addEventForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert("Submitted");
            const formData = new FormData(this);
            fetch('./index.php?page=admin_events', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const newEvent = data.event;
                    const eventCard = `
                        <div class="col-md-4 mb-4" id="event-card-${newEvent.event_id}">
                            <div class="card shadow-sm" onclick="showEventDetails(${newEvent.event_id})">
                                <img src="${newEvent.event_image}" class="card-img-top" alt="Event Image">
                                <div class="card-body">
                                    <h5 class="card-title">${newEvent.event_name}</h5>
                                    <p class="card-text text-truncate">${newEvent.event_description}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    document.querySelector('.row').insertAdjacentHTML('beforeend', eventCard);
                    document.getElementById('addEventModal').querySelector('.btn-close').click(); // Close the modal
                }
            });
        });
    </script>
</body>
</html>
