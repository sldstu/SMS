<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../database/database.class.php';
$conn = (new Database())->connect();

// Handle POST request to update sport
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sport_id = $_POST['sport_id'];
    $sport_name = $_POST['sport_name'];

    $query = $conn->prepare("UPDATE sports SET sport_name = :sport_name WHERE sport_id = :sport_id");
    $query->bindParam(':sport_name', $sport_name);
    $query->bindParam(':sport_id', $sport_id);

    if ($query->execute()) {
        echo json_encode(['success' => true, 'sport_name' => $sport_name, 'sport_id' => $sport_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['sport_id'])) {
    $sport_id = $_GET['sport_id'];

    $query = $conn->prepare("SELECT * FROM sports WHERE sport_id = :sport_id");
    $query->bindParam(':sport_id', $sport_id);
    $query->execute();
    $sport = $query->fetch(PDO::FETCH_ASSOC);

    if ($sport) {
        ?>
        <!-- Modal Form for Editing Sport -->
        <form id="editSportForm" class="needs-validation" novalidate>
            <input type="hidden" name="sport_id" id="edit_sport_id" value="<?= htmlspecialchars($sport['sport_id']) ?>">

            <div class="mb-3">
                <label for="edit_sport_name" class="form-label">Sport Name</label>
                <input type="text" class="form-control" id="edit_sport_name" name="sport_name" value="<?= htmlspecialchars($sport['sport_name']) ?>" required>
                <div class="invalid-feedback">
                    Please enter a sport name.
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
