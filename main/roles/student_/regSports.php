<?php
require_once '../MAIN/database/database.class.php';
require_once '../MAIN/includes/clean_function.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$conn = (new Database())->connect();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in first.");
}

$student_id = $_SESSION['user_id'];

// Fetch registered sports
$query = $conn->prepare("
    SELECT s.sport_name, r.status
    FROM sports s
    JOIN registrations r ON s.sport_id = r.sport_id
    WHERE r.student_id = :student_id
");
$query->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$query->execute();
$registeredSports = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Sports</title>
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">Registered Sports</h2>

    <?php if (!empty($registeredSports)): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Sport Name</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registeredSports as $sport): ?>
                    <tr>
                        <td><?= htmlspecialchars($sport['sport_name']) ?></td>
                        <td><?= htmlspecialchars($sport['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">You have not registered for any sports yet.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>