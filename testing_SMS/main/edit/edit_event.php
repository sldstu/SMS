<?php
session_start();
if ($_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

require_once 'database.class.php';
$conn = (new Database())->connect();

$event_name = ''; // Initialize the variable to avoid undefined variable warning
$event_date = ''; // Initialize the variable to avoid undefined variable warning

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];

    $query = $conn->prepare("UPDATE events SET event_name = :event_name, event_date = :event_date WHERE event_id = :event_id AND teacher_id = :teacher_id");
    $query->bindParam(':event_name', $event_name);
    $query->bindParam(':event_date', $event_date);
    $query->bindParam(':event_id', $event_id);
    $query->bindParam(':teacher_id', $_SESSION['user_id']);
    $query->execute();

    header('Location: teacher_dashboard.php');
    exit();
} else {
    if (isset($_GET['event_id'])) {
        $event_id = $_GET['event_id'];

        $query = $conn->prepare("SELECT * FROM events WHERE event_id = :event_id AND teacher_id = :teacher_id");
        $query->bindParam(':event_id', $event_id);
        $query->bindParam(':teacher_id', $_SESSION['user_id']);
        $query->execute();
        $event = $query->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            $event_name = $event['event_name'];
            $event_date = $event['event_date'];
        } else {
            echo "Event not found.";
            exit();
        }
    } else {
        echo "Invalid request.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    <h1>Edit Event</h1>
    <form action="edit_event.php" method="post">
        <input type="hidden" name="event_id" value="<?= $event_id ?>">
        <label for="event_name">Event Name</label>
        <input type="text" id="event_name" name="event_name" value="<?= htmlspecialchars($event_name) ?>" required>
        <label for="event_date">Event Date</label>
        <input type="date" id="event_date" name="event_date" value="<?= htmlspecialchars($event_date) ?>" required>
        <button type="submit">Save Changes</button>
        <button type="button" onclick="window.history.back()">Back</button>
    </form>
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
