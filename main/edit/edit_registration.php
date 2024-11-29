<?php
session_start();
if ($_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

require_once 'database.class.php';
$conn = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $registration_id = $_POST['registration_id'];
    $course = $_POST['course'];
    $section = $_POST['section'];
    $sport_id = $_POST['sport_id']; // Assuming sport_id is the correct column name

    $query = $conn->prepare("UPDATE registrations SET course = :course, section = :section, sport_id = :sport_id WHERE registration_id = :registration_id");
    $query->bindParam(':course', $course);
    $query->bindParam(':section', $section);
    $query->bindParam(':sport_id', $sport_id);
    $query->bindParam(':registration_id', $registration_id);
    $query->execute();

    header('Location: teacher_dashboard.php');
    exit();
} else {
    $registration_id = $_GET['registration_id'];

    $query = $conn->prepare("SELECT r.*, s.sport_name, u.first_name, u.last_name, r.name, r.sex, r.course, r.section FROM registrations r JOIN sports s ON r.sport_id = s.sport_id JOIN users u ON r.student_id = u.user_id WHERE r.registration_id = :registration_id");
    $query->bindParam(':registration_id', $registration_id);
    $query->execute();
    $registration = $query->fetch(PDO::FETCH_ASSOC);

    // Fetch all sports for dropdown
    $sportsQuery = $conn->prepare("SELECT * FROM sports");
    $sportsQuery->execute();
    $sports = $sportsQuery->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    <h1>Edit Registration</h1>
    <form action="edit_registration.php" method="post">
        <input type="hidden" name="registration_id" value="<?= $registration['registration_id'] ?>">
        <p>Name: <?= $registration['first_name'] . ' ' . $registration['last_name'] ?></p>
        <p>Sex: <?= $registration['sex'] ?></p>
        <label for="course">Course</label>
        <input type="text" id="course" name="course" value="<?= $registration['course'] ?>" required>
        <label for="section">Section</label>
        <input type="text" id="section" name="section" value="<?= $registration['section'] ?>" required>
        <label for="sport_id">Sport</label>
        <select id="sport_id" name="sport_id" required>
            <?php foreach ($sports as $sport): ?>
                <option value="<?= $sport['sport_id'] ?>" <?= $sport['sport_id'] == $registration['sport_id'] ? 'selected' : '' ?>><?= $sport['sport_name'] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Save Changes</button>
        <button type="button" onclick="window.history.back()">Back</button>
    </form>
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
