<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

// Fetch all events
$query = $conn->prepare("SELECT event_id, event_name, event_description, event_date, event_time, event_location, event_image FROM events");
$query->execute();
$events = $query->fetchAll(PDO::FETCH_ASSOC);

// Serve images from the database
if (isset($_GET['event_image_id'])) {
    $eventId = $_GET['event_image_id'];
    $query = $conn->prepare("SELECT image FROM events WHERE event_id = :event_id");
    $query->bindParam(':event_id', $eventId, PDO::PARAM_INT);
    $query->execute();
    $event = $query->fetch(PDO::FETCH_ASSOC);

    if ($event && $event['event_image']) {
        // Determine the image's MIME type
        $imageData = base64_decode($event['event_image']);
        $mimeType = 'event_image/jpeg'; // Adjust based on your image format (jpeg, png, gif, etc.)

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
        $eventdescription = $_POST['event_description'];
        $eventDate = $_POST['event_date'];
        $eventTime = $_POST['event_time'];
        $eventLocation = isset($_POST['event_location']) ? $_POST['event_location'] : null; // Ensure location is captured

        // Handle image upload as base64
        $imageData = null;
        if (!empty($_FILES['event_image']['name'])) {
            $imageData = base64_encode(file_get_contents($_FILES['event_image']['tmp_name'])); // Convert the image to base64 string
        }

        // Insert the event into the database
        $query = $conn->prepare("INSERT INTO events (event_name, event_description, event_date, event_time, event_location, event_image) VALUES (:event_name, :event_description, :event_date, :event_time, :event_location, :event_image)");
        $query->bindParam(':event_name', $eventName);
        $query->bindParam(':event_description', $eventdescription);
        $query->bindParam(':event_date', $eventDate);
        $query->bindParam(':event_time', $eventTime);
        $query->bindParam(':event_location', $eventLocation); // Ensure location is passed correctly
        $query->bindParam(':event_image', $imageData); // Store the base64 encoded image
        $query->execute();

        // Return the new event
        $newEventId = $conn->lastInsertId();
        $newEvent = [
            'event_id' => $newEventId,
            'event_name' => $eventName,
            'event_description' => $eventdescription,
            'event_date' => $eventDate,
            'event_time' => $eventTime,
            'event_location' => $eventLocation, // Ensure location is returned
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
            $event['event_image'] = base64_encode($event['event_image']); // Ensure the image is Base64 encoded
            echo json_encode(['status' => 'success', 'event' => $event]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Event not found.']);
        }
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
        $eventId = $_POST['event_id'];
        $eventName = $_POST['event_name'];
        $eventdescription = $_POST['event_description'];
        $eventDate = $_POST['event_date'];
        $eventTime = $_POST['event_time'];
        $eventLocation = isset($_POST['event_location']) ? $_POST['event_location'] : null; // Ensure location is passed correctly

        // Validate inputs (check if any input is empty)
        if (empty($eventName) || empty($eventdescription) || empty($eventDate) || empty($eventTime)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit();
        }

        $query = $conn->prepare("UPDATE events SET event_name = :event_name, event_description = :event_description, event_date = :event_date, event_time = :event_time, event_location = :event_location WHERE event_id = :event_id");
        $query->bindParam(':event_name', $eventName);
        $query->bindParam(':event_description', $eventdescription);
        $query->bindParam(':event_date', $eventDate);
        $query->bindParam(':event_time', $eventTime);
        $query->bindParam(':event_location', $eventLocation); // Ensure location is updated correctly
        $query->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $query->execute();

        echo json_encode(['status' => 'success', 'event' => $_POST]);
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
    <title>Admin Dashboard</title>
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
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.7);
            /* Green glow effect on hover */
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
            max-height: 40px;
            /* Limit height to avoid overflow */
            overflow: hidden;
            text-overflow: ellipsis;
            /* Adds ellipsis if the text is too long */
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
            max-height: 70vh;
            /* Set a maximum height for the modal */
            overflow-y: auto;
            /* Enable vertical scrolling if content overflows */
        }

        #event-description {
            white-space: normal;
            /* Allow multiline description */
            word-wrap: break-word;
            /* Allow text to break and wrap in the container */
        }
    </style>
</head>

<body class="bg-light">

    <div class="container my-5">
        <h1 class="text-center text-maroon">Events</h1>
        <br>

        <!-- Button to add a new event -->
        <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#addEventModal">Add Event</button>

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

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="event-name"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img id="event-image" alt="Event Image" class="mb-4">
                    <p><strong>Description:</strong> <span id="event-description"></span></p>
                    <p><strong>Time:</strong> <span id="event-time"></span></p>
                    <p><strong>Location:</strong> <span id="event-location"></span></p>
                    <p><strong>Date:</strong> <span id="event-date"></span></p>
                </div>
                <div class="modal-footer">
                    <!-- Change this line to use the current event's ID -->
                    <button class="btn btn-warning btn-sm edit-event-btn px-3" id="editEventBtn">Edit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Content populated dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addEventForm" enctype="multipart/form-data">
                    <div
                        alert("Submitted");class="modal-header">
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
                            <input type="text" class="form-control" name="event_location" id="event_location" required>
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
        let eventDetails = <?php echo json_encode($events); ?>;
        function showEventDetails(eventId) {
            const event = eventDetails.find(e => e.event_id == eventId);
            if (event) {
                document.getElementById('event-name').innerText = event.event_name;
                document.getElementById('event-time').innerText = event.event_time;
                document.getElementById('event-location').innerText = event.event_location;
                document.getElementById('event-date').innerText = event.event_date;
                document.getElementById('event-description').innerText = event.event_description;
                document.getElementById('event-image').src = 'data:image/jpeg;base64,' + event.event_image;
                // Add this line to set the current event ID
                document.getElementById('editEventBtn').setAttribute('data-event-id', event.event_id);
                new bootstrap.Modal(document.getElementById('eventDetailsModal')).show();
            }
        }

        // Load Edit Form into Modal
        // Edit Event Handler
        function editEvent(eventId) {
            fetch(`edit_event_admin.php?event_id=${eventId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const event = data.event;
                        // Populate Edit Form with event data
                        document.getElementById('edit_event_name').value = event.event_name;
                        document.getElementById('edit_event_description').value = event.event_description;
                        document.getElementById('edit_event_date').value = event.event_date;
                        document.getElementById('edit_event_time').value = event.event_time;
                        document.getElementById('edit_event_location').value = event.event_location;
                        document.getElementById('edit_event_id').value = event.event_id;
                    }
                })
                .catch(error => console.error('Error loading event details for edit:', error));

            // Show Edit Modal
            const editEventModal = new bootstrap.Modal(document.getElementById('editEventModal'));
            editEventModal.show();
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('editEventBtn').addEventListener('click', (e) => {
                e.preventDefault();
                const eventId = e.target.getAttribute('data-event-id');
                fetch('../main/edit/edit_event_admin.php?event_id=' + eventId)
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector('#editEventModal .modal-body').innerHTML = html;
                        new bootstrap.Modal(document.getElementById('editEventModal')).show();
                    });
            });
        });


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
                                <img src="${newEvent.image}" class="card-img-top" alt="Event Image">
                                <div class="card-body">
                                    <h5 class="card-title">${newEvent.event_name}</h5>
                                    <p class="card-text text-truncate">${newEvent.description}</p>
                                </div>
                            </div>
                        </div>
                    `;
                        document.querySelector('.row').insertAdjacentHTML('beforeend', eventCard);
                        document.getElementById('addEventModal').querySelector('.btn-close').click(); // Close the modal
                    }
                });
        });

        document.addEventListener('DOMContentLoaded', () => {
    // Add event listener for the edit form submission
    document.querySelector('#editEventModal').addEventListener('submit', function(e) {
        if (e.target.matches('#editEventForm')) {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            fetch('../main/edit/edit_event_admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the page or update the UI
                    location.reload();
                } else {
                    alert(data.message || 'Error updating event');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the event');
            });
        }
    });
});

    </script>

</body>

</html>
