    <?php
    if (session_status() === PHP_SESSION_NONE) {
    session_start();
    }
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
    }

    require_once __DIR__ . '/../../database/database.class.php';
    $conn = (new Database())->connect();

    // Fetch all events
    $query = $conn->prepare("SELECT event_id, event_name, event_date, teacher_id, time, location, facilitator, image FROM events");
    $query->execute();
    $events = $query->fetchAll(PDO::FETCH_ASSOC);

    // Fetch sports for each event
    $sports_query = $conn->prepare("SELECT sport_id, sport_name, event_id, sport_date, sport_time, sport_location, sport_facilitator, sport_image FROM sports");
    $sports_query->execute();
    $sports = $sports_query->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['delete_event'])) {
            $event_id = $_POST['event_id'];
            $query = $conn->prepare("DELETE FROM events WHERE event_id = :event_id");
            $query->bindParam(':event_id', $event_id);
            $query->execute();
            header("Refresh:0");
        } elseif (isset($_POST['add_event'])) {
            // Add event logic
            $event_name = $_POST['event_name'];
            $teacher_id = $_POST['teacher_id'];
            $event_date = $_POST['event_date'];
            $time = $_POST['time'];
            $location = $_POST['location'];
            $facilitator = $_POST['facilitator'];
            $image = null;

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $targetDir = "uploads/";
                $image_name = basename($_FILES['image']['name']);
                $targetFilePath = $targetDir . $image_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                    $image = $targetFilePath;
                } else {
                    echo "Error uploading image.";
                    exit();
                }
            }

            $query = $conn->prepare("INSERT INTO events (event_name, teacher_id, event_date, time, location, facilitator, image) 
                                    VALUES (:event_name, :teacher_id, :event_date, :time, :location, :facilitator, :image)");
            $query->bindParam(':event_name', $event_name);
            $query->bindParam(':teacher_id', $teacher_id);
            $query->bindParam(':event_date', $event_date);
            $query->bindParam(':time', $time);
            $query->bindParam(':location', $location);
            $query->bindParam(':facilitator', $facilitator);
            $query->bindParam(':image', $image);
            $query->execute();
            header("Refresh:0");
        } elseif (isset($_POST['add_sport'])) {
            // Add sport logic
            $sport_name = $_POST['sport_name'];
            $event_id = $_POST['event_id'];
            $sport_date = $_POST['sport_date'];
            $sport_time = $_POST['sport_time'];
            $sport_location = $_POST['sport_location'];
            $sport_facilitator = $_POST['sport_facilitator'];
            $sport_image = null;

            if (isset($_FILES['sport_image']) && $_FILES['sport_image']['error'] == 0) {
                $targetDir = "uploads/";
                $sport_image_name = basename($_FILES['sport_image']['name']);
                $targetFilePath = $targetDir . $sport_image_name;

                if (move_uploaded_file($_FILES['sport_image']['tmp_name'], $targetFilePath)) {
                    $sport_image = $targetFilePath;
                } else {
                    echo "Error uploading sport image.";
                    exit();
                }
            }

            $query = $conn->prepare("INSERT INTO sports (sport_name, event_id, sport_date, sport_time, sport_location, sport_facilitator, sport_image) 
                                    VALUES (:sport_name, :event_id, :sport_date, :sport_time, :sport_location, :sport_facilitator, :sport_image)");
            $query->bindParam(':sport_name', $sport_name);
            $query->bindParam(':event_id', $event_id);
            $query->bindParam(':sport_date', $sport_date);
            $query->bindParam(':sport_time', $sport_time);
            $query->bindParam(':sport_location', $sport_location);
            $query->bindParam(':sport_facilitator', $sport_facilitator);
            $query->bindParam(':sport_image', $sport_image);
            $query->execute();
            header("Refresh:0");
        }
        }
    ?>
        <link rel="stylesheet" href="css/events.css">
        <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard</title>
        <!-- Add Bootstrap CSS link -->
         <link rel="stylesheet" href="/sms/sms/css/style.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>

        <div class="container my-5">

            <!-- Events Section -->
            <div id="events_section" class="dashboard-section">
                <h2 class="mb-4">All Events</h2>

                <!-- Add Event and Add Sport Buttons -->
                <div class="row mb-4">
                    <div class="col-12 col-md-6 mb-2">
                        <button onclick="showAddEventForm()" class="btn btn-primary w-100">Add Event</button>
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <button onclick="showAddSportForm()" class="btn btn-secondary w-100">Add Sport</button>
                    </div>
                </div>

                <!-- Add Event Form (Initially Hidden) -->
                <div id="addEventForm" style="display:none;">
                    <h3>Add Event</h3>
                    <form action="admin_dashboard.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="teacher_id" class="form-label">Teacher ID:</label>
                            <input type="number" name="teacher_id" id="teacher_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="event_name" class="form-label">Event Name:</label>
                            <input type="text" name="event_name" id="event_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="event_date" class="form-label">Event Date:</label>
                            <input type="date" name="event_date" id="event_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="time" class="form-label">Event Time:</label>
                            <input type="time" name="time" id="time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Event Location:</label>
                            <input type="text" name="location" id="location" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="facilitator" class="form-label">Facilitator:</label>
                            <input type="text" name="facilitator" id="facilitator" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Event Image:</label>
                            <input type="file" name="image" id="image" accept="image/*" class="form-control">
                        </div>
                        <div class="button-container">
                            <button type="submit" name="add_event" class="btn btn-success">Add Event</button>
                            <button type="button" onclick="hideAddEventForm()" class="btn btn-danger">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Add Sport Form (Initially Hidden) -->
                <div id="addSportForm" style="display:none;">
                    <h3>Add Sport</h3>
                    <form action="admin_dashboard.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="sport_name" class="form-label">Sport Name:</label>
                            <input type="text" name="sport_name" id="sport_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="event_id" class="form-label">Event ID:</label>
                            <input type="number" name="event_id" id="event_id" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="sport_date" class="form-label">Sport Date:</label>
                            <input type="date" name="sport_date" id="sport_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="sport_time" class="form-label">Sport Time:</label>
                            <input type="time" name="sport_time" id="sport_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="sport_location" class="form-label">Sport Location:</label>
                            <input type="text" name="sport_location" id="sport_location" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="sport_facilitator" class="form-label">Sport Facilitator:</label>
                            <input type="text" name="sport_facilitator" id="sport_facilitator" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="sport_image" class="form-label">Sport Image:</label>
                            <input type="file" name="sport_image" id="sport_image" accept="image/*" class="form-control">
                        </div>
                        <div class="button-container">
                            <button type="submit" name="add_sport" class="btn btn-success">Add Sport</button>
                            <button type="button" onclick="hideAddSportForm()" class="btn btn-danger">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Event Table -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Event ID</th>
                                <th>Event Name</th>
                                <th>Event Date</th>
                                <th>Teacher ID</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Facilitator</th>
                                <th>Image</th>
                                <th>Actions</th>
                                <th>Sports</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?= $event['event_id'] ?></td>
                                    <td><?= $event['event_name'] ?></td>
                                    <td><?= $event['event_date'] ?></td>
                                    <td><?= $event['teacher_id'] ?></td>
                                    <td><?= $event['time'] ?></td>
                                    <td><?= $event['location'] ?></td>
                                    <td><?= $event['facilitator'] ?></td>
                                    <td>
                                        <?php if ($event['image']): ?>
                                            <img src="<?= $event['image'] ?>" alt="Event Image" width="100">
                                        <?php else: ?>
                                            No Image
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="sms_main/sms/main/edit_event_admin.php" method="get" style="display:inline;">
                                            <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                            <button type="submit" class="btn btn-warning btn-sm">Edit</button>
                                        </form>
                                        <form action="admin_dashboard.php" method="post" style="display:inline;">
                                            <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                            <button type="submit" name="delete_event" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                    <td>
                                        <button onclick="showSportsForm(<?= $event['event_id'] ?>)" class="btn btn-info btn-sm">View</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- View Sports Form (Initially Hidden) -->
                <div id="viewSportsForm" style="display:none;">
                    <h3>Sports for Event ID: <span id="event_id"></span></h3>
                    <div id="sportsList"></div>
                    <div class="button-container">
                        <button type="button" onclick="hideSportsForm()" class="btn btn-secondary">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function showAddEventForm() {
                document.getElementById('addEventForm').style.display = 'block';
            }

            function hideAddEventForm() {
                document.getElementById('addEventForm').style.display = 'none';
            }

            function showAddSportForm() {
                document.getElementById('addSportForm').style.display = 'block';
            }

            function hideAddSportForm() {
                document.getElementById('addSportForm').style.display = 'none';
            }

            function showSportsForm(eventId) {
                document.getElementById('event_id').textContent = eventId;
                fetchSports(eventId);
                document.getElementById('viewSportsForm').style.display = 'block';
            }

            function hideSportsForm() {
                document.getElementById('viewSportsForm').style.display = 'none';
            }

            function fetchSports(eventId) {
                fetch('get_sports.php?event_id=' + eventId)
                    .then(response => response.json())
                    .then(data => {
                        let sportsList = '';
                        data.forEach(sport => {
                            sportsList += `
                                <div class="sport-item">
                                    <div class="sport-details">
                                        <p>Sport Name: ${sport.sport_name}</p>
                                        <p>Sport Date: ${sport.sport_date}</p>
                                        <p>Sport Time: ${sport.sport_time}</p>
                                        <p>Sport Location: ${sport.sport_location}</p>
                                        <p>Sport Facilitator: ${sport.sport_facilitator}</p>
                                        <p>Sport Image: <img src="${sport.sport_image}" alt="Sport Image" width="100"></p>
                                    </div>
                                </div>
                            `;
                        });
                        document.getElementById('sportsList').innerHTML = sportsList;
                    })
                    .catch(error => console.error('Error fetching sports:', error));
            }
        </script>
        <!-- Add Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
