<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '../../database/database.class.php';
$conn = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $sex = $_POST['sex'];
    $course = $_POST['course'];
    $section = $_POST['section'];
    $birthday = $_POST['birthday'];
    $address = $_POST['address'];
    $contact_info = $_POST['contact_info'];

    $cor = file_get_contents($_FILES['cor']['tmp_name']);
    $id_image = file_get_contents($_FILES['id_image']['tmp_name']);
    #$medcert = file_get_contents($_FILES['medcert']['tmp_name']);

    $query = $conn->prepare("INSERT INTO student_details (user_id, sex, course, section, birthday, address, contact_info, cor, id_image) VALUES (:user_id, :sex, :course, :section, :birthday, :address, :contact_info, :cor, :id_image)");
    $query->bindParam(':user_id', $user_id);
    $query->bindParam(':sex', $sex);
    $query->bindParam(':course', $course);
    $query->bindParam(':section', $section);
    $query->bindParam(':birthday', $birthday);
    $query->bindParam(':address', $address);
    $query->bindParam(':contact_info', $contact_info);
    $query->bindParam(':cor', $cor, PDO::PARAM_LOB);
    $query->bindParam(':id_image', $id_image, PDO::PARAM_LOB);
    #$query->bindParam(':medcert', $medcert, PDO::PARAM_LOB);

    if ($query->execute()) {
        $_SESSION['profile_incomplete'] = false;
        header("Location: ../../SMS/index.php");
        exit();
    } else {
        $error = "Database error. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow p-4" style="width: 100%; max-width: 500px;">
            <h2 class="text-center mb-4">Complete Your Profile</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form action="complete_profile.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="mb-3">
                    <label for="id_image" class="form-label">ID</label>
                    <input type="file" class="form-control" id="id_image" name="id_image" required>
                </div>
                <!--<div class="mb-3">
                    <label for="medcert" class="form-label">Medical Certificate</label>
                    <input type="file" class="form-control" id="medcert" name="medcert" required>
                </div>-->
                <div class="mb-3">
                    <label for="sex" class="form-label">Sex</label>
                    <select class="form-select" id="sex" name="sex" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Female">Others</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="course" class="form-label">Course</label>
                    <select class="form-select" id="course" name="course" required>
                        <option value="ACT">ACT</option>
                        <option value="CS">CS</option>
                        <option value="IT">IT</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="section" class="form-label">Section</label>
                    <select class="form-select" id="section" name="section" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="birthday" class="form-label">Birthday</label>
                    <input type="date" class="form-control" id="birthday" name="birthday" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" required>
                </div>
                <div class="mb-3">
                    <label for="contact_info" class="form-label">Contact Info</label>
                    <input type="text" class="form-control" id="contact_info" name="contact_info" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
