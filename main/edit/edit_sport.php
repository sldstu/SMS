<?php
session_start();
if ($_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '../../database/database.class.php';
$conn = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sport_id = $_POST['sport_id'];
    $sport_name = $_POST['sport_name'];
    $sport_description = $_POST['sport_description'];

    $query = $conn->prepare("UPDATE sports SET sport_name = :sport_name, sport_description = :sport_description WHERE sport_id = :sport_id");
    $query->bindParam(':sport_name', $sport_name);
    $query->bindParam(':sport_description', $sport_description);
    $query->bindParam(':sport_id', $sport_id);

    if ($query->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit();
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['sport_id'])) {
    $sport_id = $_GET['sport_id'];

    $query = $conn->prepare("SELECT * FROM sports WHERE sport_id = :sport_id");
    $query->bindParam(':sport_id', $sport_id);
    $query->execute();
    $sport = $query->fetch(PDO::FETCH_ASSOC);

    if ($sport) {
        ?>
        <!-- Include Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="/SMS/css/style.css">

        <!-- Bootstrap-styled form -->
        <form id="editSportForm" class="needs-validation" novalidate>
            <input type="hidden" name="sport_id" value="<?= htmlspecialchars($sport['sport_id']) ?>">

            <div class="mb-3">
                <label for="sport_name" class="form-label">Sport Name</label>
                <input type="text" class="form-control" id="sport_name" name="sport_name" value="<?= htmlspecialchars($sport['sport_name']) ?>" required>
                <div class="invalid-feedback">
                    Please enter a sport name.
                </div>
            </div>

            <div class="mb-3">
                <label for="sport_description" class="form-label">Sport Description</label>
                <textarea class="form-control" id="sport_description" name="sport_description" rows="3" required><?= htmlspecialchars($sport['sport_description']) ?></textarea>
                <div class="invalid-feedback">
                    Please enter a sport description.
                </div>
            </div>
        </form>

        <!-- Bootstrap JS (if required for modals) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <?php
    } else {
        echo "Sport not found.";
    }
    exit();
}
?>
