<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '../../database/database.class.php';
$conn = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    $query = $conn->prepare("UPDATE users SET username = :username, role = :role, first_name = :first_name, last_name = :last_name WHERE user_id = :user_id");
    $query->bindParam(':username', $username);
    $query->bindParam(':role', $role);
    $query->bindParam(':first_name', $first_name);
    $query->bindParam(':last_name', $last_name);
    $query->bindParam(':user_id', $user_id);

    if ($query->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit();
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    $query = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $query->bindParam(':user_id', $user_id);
    $query->execute();
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        ?>
        <!-- Include Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="/SMS/css/style.css">

        <!-- Bootstrap-styled form -->
        <form id="editUserForm" class="needs-validation" novalidate>
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                <div class="invalid-feedback">
                    Please enter a username.
                </div>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <!-- <input type="text" class="form-control" id="role" name="role" value="<?= htmlspecialchars($user['role']) ?>" required> -->
                <select class="form-select" id="role" name="role" value="<?= htmlspecialchars($user['role']) ?>" required>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                    <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                </select>
                <div class="invalid-feedback">
                    Please specify the role.
                </div>
            </div>

            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                <div class="invalid-feedback">
                    Please provide the first name.
                </div>
            </div>

            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                <div class="invalid-feedback">
                    Please provide the last name.
                </div>
            </div>
        </form>

        <!-- Bootstrap JS (if required for modals) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <?php
    } else {
        echo "User not found.";
    }
    exit();
}
?>
