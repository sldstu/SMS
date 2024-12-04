<?php
session_start();
if ($_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

require_once 'database.class.php';
$conn = (new Database())->connect();

// Fetch the sport ID from the URL
$sport_id = $_GET['sport_id'] ?? null;
if (!$sport_id) {
    echo "No sport selected.";
    exit();
}

// Get sport details
$query = $conn->prepare("SELECT * FROM sports WHERE sport_id = :sport_id");
$query->bindParam(':sport_id', $sport_id);
$query->execute();
$sport = $query->fetch(PDO::FETCH_ASSOC);

if ($sport) {
    // Register the student for this sport (you can modify this part to suit your registration process)
    $student_id = $_SESSION['user_id'];
    $query = $conn->prepare("INSERT INTO sport_registrations (student_id, sport_id) VALUES (:student_id, :sport_id)");
    $query->bindParam(':student_id', $student_id);
    $query->bindParam(':sport_id', $sport_id);
    $query->execute();
    echo "Successfully registered for " . $sport['sport_name'];
} else {
    echo "Invalid sport.";
}
?>
