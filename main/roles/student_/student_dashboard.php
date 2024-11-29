<?php
// Set the content page to this specific student dashboard
$contentPage = 'student_dashboard_content.php'; // Adjust if the content is elsewhere
// include 'layout.php';

session_start();
if ($_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

require_once '../../database/database.class.php';
$conn = (new Database())->connect();
$student_id = $_SESSION['user_id'];

// Fetch registered sports
$query = $conn->prepare("SELECT s.sport_name, r.status FROM sports s JOIN registrations r ON s.sport_id = r.sport_id WHERE r.student_id = :student_id");
$query->bindParam(':student_id', $student_id);
$query->execute();
$registrations = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch available sports
$sportsQuery = $conn->prepare("SELECT * FROM sports");
$sportsQuery->execute();
$sports = $sportsQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch all events
$eventsQuery = $conn->prepare("SELECT * FROM events");
$eventsQuery->execute();
$events = $eventsQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch available courses and sections
$coursesQuery = $conn->prepare("SELECT course_id, course_name FROM courses");
$coursesQuery->execute();
$courses = $coursesQuery->fetchAll(PDO::FETCH_ASSOC);

$sectionsQuery = $conn->prepare("SELECT section_name, course_id FROM sections");
$sectionsQuery->execute();
$sections = $sectionsQuery->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_sport'])) {
    $sport_id = $_POST['sport_id'];
    $name = $_POST['name'];
    $sex = $_POST['sex'];
    $course = $_POST['course'];
    $section = $_POST['section'];

    // Registration query to include additional fields
    $registerQuery = $conn->prepare("INSERT INTO registrations (student_id, sport_id, name, sex, course, section) VALUES (:student_id, :sport_id, :name, :sex, :course, :section)");
    $registerQuery->bindParam(':student_id', $student_id);
    $registerQuery->bindParam(':sport_id', $sport_id);
    $registerQuery->bindParam(':name', $name);
    $registerQuery->bindParam(':sex', $sex);
    $registerQuery->bindParam(':course', $course);
    $registerQuery->bindParam(':section', $section);
    $registerQuery->execute();
    header('Location: student_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function updateSections() {
            var courseId = document.getElementById('course').value;
            var sectionSelect = document.getElementById('section');
            sectionSelect.innerHTML = '';

            var sections = <?php echo json_encode($sections); ?>;
            for (var i = 0; i < sections.length; i++) {
                if (sections[i].course_id == courseId) {
                    var option = document.createElement('option');
                    option.value = sections[i].section_name;
                    option.text = sections[i].section_name;
                    sectionSelect.appendChild(option);
                }
            }

            if (sectionSelect.options.length == 0) {
                var option = document.createElement('option');
                option.value = '';
                option.text = 'N/A';
                sectionSelect.appendChild(option);
            }
        }

        function showRegistrationForm(sport_id) {
            document.getElementById('sport_id').value = sport_id;
            document.getElementById('registration_form').style.display = 'block';
        }

        function showSection(sectionId) {
            var sections = document.getElementsByClassName('dashboard-section');
            for (var i = 0; i < sections.length; i++) {
                sections[i].style.display = 'none';
            }
            document.getElementById(sectionId).style.display = 'block';
        }
    </script>
</head>
<body>
    <!-- <?php require_once 'includes/header.php'; ?> -->
    <h1>Welcome, <?= $_SESSION['first_name'] ?></h1>
    <a href="logout.php">Logout</a>

    <div class="dashboard-menu">
        <button onclick="showSection('registered_sports_section')">Registered Sports</button>
        <button onclick="showSection('sports_section')">Sports</button>
        <button onclick="showSection('events_section')">Upcoming Events</button>
    </div>

    <div id="registered_sports_section" class="dashboard-section" style="display:none;">
        <h2>Registered Sports</h2>
        <table>
            <thead>
                <tr>
                    <th>Sport</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registrations as $registration): ?>
                    <tr>
                        <td><?= $registration['sport_name'] ?></td>
                        <td><?= ucfirst($registration['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="sports_section" class="dashboard-section" style="display:none;">
        <h2>Available Sports</h2>
        <table>
            <thead>
                <tr>
                    <th>Sport Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sports as $sport): ?>
                    <tr>
                        <td><?= $sport['sport_name'] ?></td>
                        <td>
                            <button onclick="showRegistrationForm(<?= $sport['sport_id'] ?>)">Register</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div id="registration_form" style="display:none;">
            <h3>Register for Sport</h3>
            <form action="student_dashboard.php" method="post">
                <input type="hidden" id="sport_id" name="sport_id">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
                <label for="sex">Sex</label>
                <select id="sex" name="sex" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
                <label for="course">Course</label>
                <select id="course" name="course" onchange="updateSections()" required>
                    <option value="CS">CS</option>
                    <option value="IT">IT</option>
                    <option value="ACT">ACT</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['course_id'] ?>"><?= $course['course_name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="section">Section</label>
                <select id="section" name="section" required>
                    <option value="CS2">CS2</option>
                    <!-- Section options will be populated based on the selected course -->
                </select>
                <button type="submit" name="register_sport">Submit</button>
            </form>
        </div>
    </div>

    <div id="events_section" class="dashboard-section" style="display:none;">
        <h2>Upcoming Events</h2>
        <table>
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= $event['event_name'] ?></td>
                        <td><?= $event['event_date'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
