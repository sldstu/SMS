<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once 'database.class.php';
$conn = (new Database())->connect();

$course_name = '';
$sections = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_course'])) {
        $course_id = $_POST['course_id'];
        $course_name = $_POST['course_name'];

        $query = $conn->prepare("UPDATE courses SET course_name = :course_name WHERE course_id = :course_id");
        $query->bindParam(':course_name', $course_name);
        $query->bindParam(':course_id', $course_id);
        $query->execute();

        header('Location: success_page.php?message=Course updated successfully!');
        exit();
    } elseif (isset($_POST['add_section'])) {
        $section_name = $_POST['section_name'];
        $course_id = $_POST['course_id'];

        $query = $conn->prepare("INSERT INTO sections (section_name, course_id) VALUES (:section_name, :course_id)");
        $query->bindParam(':section_name', $section_name);
        $query->bindParam(':course_id', $course_id);
        $query->execute();

        header('Location: success_page.php?message=Section added successfully!');
        exit();
    } elseif (isset($_POST['delete_section'])) {
        $section_id = $_POST['section_id'];
        $query = $conn->prepare("DELETE FROM sections WHERE section_id = :section_id");
        $query->bindParam(':section_id', $section_id);
        $query->execute();

        header('Location: success_page.php?message=Section deleted successfully!');
        exit();
    }
} else {
    if (isset($_GET['course_id'])) {
        $course_id = $_GET['course_id'];

        $query = $conn->prepare("SELECT * FROM courses WHERE course_id = :course_id");
        $query->bindParam(':course_id', $course_id);
        $query->execute();
        $course = $query->fetch(PDO::FETCH_ASSOC);

        if ($course) {
            $course_name = $course['course_name'];

            $query = $conn->prepare("SELECT * FROM sections WHERE course_id = :course_id");
            $query->bindParam(':course_id', $course_id);
            $query->execute();
            $sections = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            echo "Course not found.";
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
    <title>Edit Course and Sections</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php require_once 'includes/header.php'; ?>
    <h1>Edit Course and Sections</h1>
    <form action="edit_course_section_admin.php" method="post">
        <input type="hidden" name="course_id" value="<?= htmlspecialchars($course_id) ?>">
        <label for="course_name">Course Name</label>
        <input type="text" id="course_name" name="course_name" value="<?= htmlspecialchars($course_name) ?>" required>
        <button type="submit" name="update_course">Save Changes</button>
    </form>

    <h2>Sections</h2>
    <table>
        <thead>
            <tr>
                <th>Section Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sections as $section): ?>
                <tr>
                    <td><?= htmlspecialchars($section['section_name']) ?></td>
                    <td>
                        <form action="edit_course_section_admin.php" method="post" style="display:inline;">
                            <input type="hidden" name="section_id" value="<?= htmlspecialchars($section['section_id']) ?>">
                            <input type="hidden" name="course_id" value="<?= htmlspecialchars($course_id) ?>">
                            <button type="submit" name="delete_section">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Add New Section</h3>
    <form action="edit_course_section_admin.php" method="post">
        <input type="hidden" name="course_id" value="<?= htmlspecialchars($course_id) ?>">
        <label for="section_name">Section Name</label>
        <input type="text" id="section_name" name="section_name" required>
        <button type="submit" name="add_section">Add Section</button>
    </form>

    <button type="button" onclick="window.history.back()">Back</button>
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
