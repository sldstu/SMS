<?php
session_start();
if ($_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

require_once 'database.class.php';
$conn = (new Database())->connect();

$sport_name = ''; // Initialize the variable to avoid undefined variable warning

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sport_id = $_POST['sport_id'];
    $sport_name = $_POST['sport_name'];

    $query = $conn->prepare("UPDATE sports SET sport_name = :sport_name WHERE sport_id = :sport_id AND teacher_id = :teacher_id");
    $query->bindParam(':sport_name', $sport_name);
    $query->bindParam(':sport_id', $sport_id);
    $query->bindParam(':teacher_id', $_SESSION['user_id']);
    $query->execute();

    header('Location: teacher_dashboard.php');
    exit();
} else {
    if (isset($_GET['sport_id'])) {
        $sport_id = $_GET['sport_id'];

        $query = $conn->prepare("SELECT * FROM sports WHERE sport_id = :sport_id AND teacher_id = :teacher_id");
        $query->bindParam(':sport_id', $sport_id);
        $query->bindParam(':teacher_id', $_SESSION['user_id']);
        $query->execute();
        $sport = $query->fetch(PDO::FETCH_ASSOC);

        if ($sport) {
            $sport_name = $sport['sport_name'];
        } else {
            echo "Sport not found.";
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
    <title>Edit Sport</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    <h1>Edit Sport</h1>
    <form action="edit_sport.php" method="post">
        <input type="hidden" name="sport_id" value="<?= htmlspecialchars($sport_id) ?>">
        <label for="sport_name">Sport Name</label>
        <input type="text" id="sport_name" name="sport_name" value="<?= htmlspecialchars($sport_name) ?>" required>
        <button type="submit">Save Changes</button>
        <button type="button" onclick="window.history.back()">Back</button>
    </form>
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
