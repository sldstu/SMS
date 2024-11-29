<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($_SESSION['role'] !== 'admin') {
    exit();
}

require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === 'delete_user') {
    header('Content-Type: application/json');

    if (isset($_POST['delete_section'])) {
      $section_id = $_POST['section_id'];
      $query = $conn->prepare("DELETE FROM sections WHERE section_id = :section_id");
      $query->bindParam(':section_id', $section_id);
      $query->execute();
      header('Location: success_page.php?message=Section deleted successfully!');
      exit();
  } elseif (isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $query = $conn->prepare("INSERT INTO courses (course_name) VALUES (:course_name)");
    $query->bindParam(':course_name', $course_name);
    $query->execute();
    header('Location: success_page.php?message=Course added successfully!');
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
}elseif (isset($_POST['delete_course'])) {
  $course_id = $_POST['course_id'];
  $query = $conn->prepare("DELETE FROM courses WHERE course_id = :course_id");
  $query->bindParam(':course_id', $course_id);
  $query->execute();
  header('Location: success_page.php?message=Course deleted successfully!');
  exit();
}


}


// Fetch all courses
$query = $conn->prepare("SELECT * FROM courses");
$query->execute();
$courses = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch all sections
$query = $conn->prepare("SELECT s.*, c.course_name FROM sections s JOIN courses c ON s.course_id = c.course_id");
$query->execute();
$sections = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/SMS/css/style.css">
</head>

<body>
    <div class="container my-5">
        <h2 class="mb-4">All Courses and Sections</h2>

        <!-- Search Bar -->
        <div class="mb-3">
            <input type="text" id="search_course_bar" class="form-control" placeholder="Search for courses or sections..." onkeyup="searchCourse()">
        </div>

        <!-- Courses Table -->
        <div class="table-responsive">
            <table id="courses_table" class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Course Name</th>
                        <th>Sections</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?= htmlspecialchars($course['course_name']) ?></td>
                            <td>
                                <?php
                                $query = $conn->prepare("SELECT section_name FROM sections WHERE course_id = :course_id");
                                $query->bindParam(':course_id', $course['course_id']);
                                $query->execute();
                                $sections = $query->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($sections as $section) {
                                    echo htmlspecialchars($section['section_name']) . " ";
                                }
                                ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <form action="../MAIN/edit/edit_course_section_admin.php" method="get">
                                        <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                                        <button type="submit" class="btn btn-warning btn-sm">Edit</button>
                                    </form>
                                    <form action="" method="post">
                                        <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                                        <button type="submit" name="delete_course" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add New Course Form -->
        <div class="mt-5">
            <h3 class="mb-3">Add New Course</h3>
            <form action="add_" method="post" class="d-flex flex-column gap-3">
                <div class="mb-3">
                    <label for="course_name" class="form-label">Course Name</label>
                    <input type="text" id="course_name" name="course_name" class="form-control" required>
                </div>
                <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
            </form>
        </div>
    </div>

    <script>
        // Search Courses and Sections
        function searchCourse() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("search_course_bar");
            filter = input.value.toUpperCase();
            table = document.getElementById("courses_table");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                tdCourse = tr[i].getElementsByTagName("td")[0];
                tdSection = tr[i].getElementsByTagName("td")[1];
                if (tdCourse || tdSection) {
                    txtValueCourse = tdCourse.textContent || tdCourse.innerText;
                    txtValueSection = tdSection.textContent || tdSection.innerText;
                    if (txtValueCourse.toUpperCase().indexOf(filter) > -1 || txtValueSection.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>


    <script>
        function showSection(sectionId) {
            var sections = document.getElementsByClassName('dashboard-section');
            for (var i = 0; i < sections.length; i++) {
                sections[i].style.display = 'none';
            }
            document.getElementById(sectionId).style.display = 'block';
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php include '../MAIN/auth/loads/confirmation.php'; ?>

</body>

</html>