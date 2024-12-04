<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once 'database.class.php';
$conn = (new Database())->connect();

// Initialize variables
$event_name = '';
$event_date = '';
$teacher_id = '';
$time = '';
$location = '';
$facilitator = '';
$image = '';  // This will hold the current image path

// Fetch the event details if event_id is provided
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    
    $query = $conn->prepare("SELECT * FROM events WHERE event_id = :event_id");
    $query->bindParam(':event_id', $event_id);
    $query->execute();
    $event = $query->fetch(PDO::FETCH_ASSOC);
    
    if ($event) {
        // Assign event values to variables
        $event_name = $event['event_name'];
        $event_date = $event['event_date'];
        $teacher_id = $event['teacher_id'];
        $time = $event['time'];
        $location = $event['location'];
        $facilitator = $event['facilitator'];
        $image = $event['image'];  // Store current image
    } else {
        echo "Event not found.";
        exit();
    }
}

// Update event if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $teacher_id = $_POST['teacher_id'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    $facilitator = $_POST['facilitator'];

    // Handle image upload if a new image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "uploads/";
        $imageFileName = basename($_FILES['image']['name']);
        $targetFilePath = $targetDir . $imageFileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $image = $targetFilePath;  // Update image path
        } else {
            echo "Error uploading image.";
            exit();
        }
    }

    // Update event details in the database
    $query = $conn->prepare("
        UPDATE events 
        SET event_name = :event_name, 
            event_date = :event_date, 
            teacher_id = :teacher_id, 
            time = :time, 
            location = :location, 
            facilitator = :facilitator
            " . ($image ? ", image = :image" : "") . "
        WHERE event_id = :event_id
    ");

    $query->bindParam(':event_name', $event_name);
    $query->bindParam(':event_date', $event_date);
    $query->bindParam(':teacher_id', $teacher_id);
    $query->bindParam(':time', $time);
    $query->bindParam(':location', $location);
    $query->bindParam(':facilitator', $facilitator);
    if ($image) {
        $query->bindParam(':image', $image);
    }
    $query->bindParam(':event_id', $event_id);
    $query->execute();

    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <link rel="stylesheet" href="css/edit-event.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    
    <h1>Edit Event</h1>
    
    <form action="edit_event_admin.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="event_id" value="<?= htmlspecialchars($event_id) ?>">

        <label for="event_name">Event Name:</label>
        <input type="text" name="event_name" id="event_name" value="<?= htmlspecialchars($event_name) ?>" required><br>

        <label for="event_date">Event Date:</label>
        <input type="date" name="event_date" id="event_date" value="<?= htmlspecialchars($event_date) ?>" required><br>

        <label for="teacher_id">Teacher ID:</label>
        <input type="number" name="teacher_id" id="teacher_id" value="<?= htmlspecialchars($teacher_id) ?>" required><br>

        <label for="time">Event Time:</label>
        <input type="time" name="time" id="time" value="<?= htmlspecialchars($time) ?>" required><br>

        <label for="location">Event Location:</label>
        <input type="text" name="location" id="location" value="<?= htmlspecialchars($location) ?>" required><br>

        <label for="facilitator">Facilitator:</label>
        <input type="text" name="facilitator" id="facilitator" value="<?= htmlspecialchars($facilitator) ?>" required><br>

        <!-- Image Upload -->
        <label for="image">Event Image (Optional):</label>
        <input type="file" name="image" id="image" accept="image/*"><br><br>

        <?php if ($image): ?>
            <p>Current Image:</p>
            <img src="<?= htmlspecialchars($image) ?>" alt="Event Image" style="max-width: 200px;"><br>
        <?php endif; ?>

        <button type="submit">Update Event</button>
        <button type="button" onclick="window.history.back()">Cancel</button>
    </form>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
                