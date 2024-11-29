<?php



session_start();
if ($_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

require_once 'database.class.php';
$conn = (new Database())->connect();
$teacher_id = $_SESSION['user_id'];

// Fetch sports managed by the teacher
$query = $conn->prepare("SELECT * FROM sports WHERE teacher_id = :teacher_id");
$query->bindParam(':teacher_id', $teacher_id);
$query->execute();
$sports = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch events managed by the teacher
$query = $conn->prepare("SELECT * FROM events WHERE teacher_id = :teacher_id");
$query->bindParam(':teacher_id', $teacher_id);
$query->execute();
$events = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending registrations
$query = $conn->prepare("SELECT r.*, s.sport_name, u.first_name, u.last_name, r.name, r.sex, r.course, r.section FROM registrations r JOIN sports s ON r.sport_id = s.sport_id JOIN users u ON r.student_id = u.user_id WHERE r.status = 'pending'");
$query->execute();
$pending_registrations = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch all registrations (approved and declined)
$query = $conn->prepare("SELECT r.*, s.sport_name, u.first_name, u.last_name, r.name, r.sex, r.course, r.section FROM registrations r JOIN sports s ON r.sport_id = s.sport_id JOIN users u ON r.student_id = u.user_id WHERE r.status IN ('approved', 'declined')");
$query->execute();
$all_registrations = $query->fetchAll(PDO::FETCH_ASSOC);

// Handle adding a new sport
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_sport'])) {
    $sport_name = $_POST['sport_name'];
    $query = $conn->prepare("INSERT INTO sports (sport_name, teacher_id) VALUES (:sport_name, :teacher_id)");
    $query->bindParam(':sport_name', $sport_name);
    $query->bindParam(':teacher_id', $teacher_id);
    $query->execute();
    header('Location: teacher_dashboard.php');
    exit();
}

// Handle adding a new event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $query = $conn->prepare("INSERT INTO events (event_name, event_date, teacher_id) VALUES (:event_name, :event_date, :teacher_id)");
    $query->bindParam(':event_name', $event_name);
    $query->bindParam(':event_date', $event_date);
    $query->bindParam(':teacher_id', $teacher_id);
    $query->execute();
    header('Location: teacher_dashboard.php');
    exit();
}

// Handle approval/decline of registrations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $registration_id = $_POST['registration_id'];
        $query = $conn->prepare("UPDATE registrations SET status = 'approved' WHERE registration_id = :registration_id");
        $query->bindParam(':registration_id', $registration_id);
        $query->execute();
        header('Location: teacher_dashboard.php');
        exit();
    } elseif (isset($_POST['decline'])) {
        $registration_id = $_POST['registration_id'];
        $query = $conn->prepare("UPDATE registrations SET status = 'declined' WHERE registration_id = :registration_id");
        $query->bindParam(':registration_id', $registration_id);
        $query->execute();
        header('Location: teacher_dashboard.php');
        exit();
    }
}

// Handle deletion of sport
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_sport'])) {
    $sport_id = $_POST['sport_id'];
    $query = $conn->prepare("DELETE FROM sports WHERE sport_id = :sport_id AND teacher_id = :teacher_id");
    $query->bindParam(':sport_id', $sport_id);
    $query->bindParam(':teacher_id', $teacher_id);
    $query->execute();
    header('Location: teacher_dashboard.php');
    exit();
}

// Handle deletion of registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_registration'])) {
    $registration_id = $_POST['registration_id'];
    $query = $conn->prepare("DELETE FROM registrations WHERE registration_id = :registration_id");
    $query->bindParam(':registration_id', $registration_id);
    $query->execute();
    header('Location: teacher_dashboard.php');
    exit();
}


// Handle deletion of event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'];
    $query = $conn->prepare("DELETE FROM events WHERE event_id = :event_id AND teacher_id = :teacher_id");
    $query->bindParam(':event_id', $event_id);
    $query->bindParam(':teacher_id', $teacher_id);
    $query->execute();
    header('Location: teacher_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <script>
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
    <?php require_once 'includes/header.php'; ?>
    <h1>Welcome, <?= $_SESSION['first_name'] ?></h1>
    <a href="logout.php">Logout</a>

    <div class="dashboard-menu">
        <button onclick="showSection('sports_section')">Sports</button>
        <button onclick="showSection('events_section')">Events</button>
        <button onclick="showSection('registrations_section')">Student Registration</button>
    </div>

    <div id="sports_section" class="dashboard-section" style="display:none;">
        <h2>Your Sports</h2>
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
                            <form action="edit_sport.php" method="get" style="display:inline;">
                                <input type="hidden" name="sport_id" value="<?= $sport['sport_id'] ?>">
                                <button type="submit">Edit</button>
                            </form>
                            <form action="teacher_dashboard.php" method="post" style="display:inline;">
                                <input type="hidden" name="sport_id" value="<?= $sport['sport_id'] ?>">
                                <button type="submit" name="delete_sport">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form action="teacher_dashboard.php" method="post">
            <h3>Add New Sport</h3>
            <label for="sport_name">Sport Name</label>
            <input type="text" id="sport_name" name="sport_name" required>
            <button type="submit" name="add_sport">Add Sport</button>
        </form>
    </div>

    <div id="events_section" class="dashboard-section" style="display:none;">
        <h2>Your Events</h2>
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= $event['event_name'] ?></td>
                        <td><?= $event['event_date'] ?></td>
                        <td>
                            <form action="edit_event.php" method="get" style="display:inline;">
                                <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                <button type="submit">Edit</button>
                            </form>
                            <form action="teacher_dashboard.php" method="post" style="display:inline;">
                                <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                <button type="submit" name="delete_event">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form action="teacher_dashboard.php" method="post">
        
            <h3>Add New Event</h3>
            <label for="event_name">Event Name</label>
            <input type="text" id="event_name" name="event_name" required>
            <label for="event_date">Event Date</label>
            <input type="date" id="event_date" name="event_date" required>
            <button type="submit" name="add_event">Add Event</button>
        </form>
    </div>

    <div id="registrations_section" class="dashboard-section" style="display:none;">
        <h2>Pending Registrations</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Sex</th>
                    <th>Course</th>
                    <th>Section</th>
                    <th>Sport</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_registrations as $registration): ?>
                    <tr>
                        <td><?= $registration['first_name'] . ' ' . $registration['last_name'] ?></td>
                        <td><?= $registration['sex'] ?></td>
                        <td><?= $registration['course'] ?></td>
                        <td><?= $registration['section'] ?></td>
                        <td><?= $registration['sport_name'] ?></td>
                        <td>
                            <form action="teacher_dashboard.php" method="post" style="display:inline;">
                                <input type="hidden" name="registration_id" value="<?= $registration['registration_id'] ?>">
                                <button type="submit" name="approve">Approve</button>
                                <button type="submit" name="decline">Decline</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>All Registrations</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Sex</th>
                    <th>Course</th>
                    <th>Section</th>
                    <th>Sport</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($all_registrations as $registration): ?>
    <tr>
        <td><?= $registration['first_name'] . ' ' . $registration['last_name'] ?></td>
        <td><?= $registration['sex'] ?></td>
        <td><?= $registration['course'] ?></td>
        <td><?= $registration['section'] ?></td>
        <td><?= $registration['sport_name'] ?></td>
        <td><?= ucfirst($registration['status']) ?></td>
        <td>
            <form action="edit_registration.php" method="get" style="display:inline;">
                <input type="hidden" name="registration_id" value="<?= $registration['registration_id'] ?>">
                <button type="submit">Edit</button>
            </form>
            <form action="teacher_dashboard.php" method="post" style="display:inline;">
                <input type="hidden" name="registration_id" value="<?= $registration['registration_id'] ?>">
                <button type="submit" name="delete_registration">Delete</button>
            </form>
        </td>
    </tr>
<?php endforeach; ?>

            </tbody>
        </table>
    </div>

<!-- Edit Form -->
<div id="edit_form" style="display:none;">
    <form action="teacher_dashboard.php" method="post">
        <input type="hidden" id="edit_type" name="edit_type">
        <input type="hidden" id="edit_id" name="edit_id">
        <label for="edit_name">Name</label>
        <input type="text" id="edit_name" name="edit_name" required>
        <label for="edit_sex">Sex</label>
        <input type="text" id="edit_sex" name="edit_sex" required>
        <label for="edit_course">Course</label>
        <input type="text" id="edit_course" name="edit_course" required>
        <label for="edit_section">Section</label>
        <input type="text" id="edit_section" name="edit_section" required>
        <label for="edit_sport">Sport</label>
        <input type="text" id="edit_sport" name="edit_sport" required>
        <button type="submit" name="edit_submit">Save Changes</button>
    </form>
</div>  

<?php require_once 'includes/footer.php'; ?>

<script>
    function showEditForm(type, id, name, sex, course, section, sport) {
        document.getElementById('edit_form').style.display = 'block';
        document.getElementById('edit_type').value = type;
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_sex').value = sex;
        document.getElementById('edit_course').value = course;
        document.getElementById('edit_section').value = section;
        document.getElementById('edit_sport').value = sport;
    }

    function showSection(sectionId) {
        var sections = document.getElementsByClassName('dashboard-section');
        for (var i = 0; i < sections.length; i++) {
            sections[i].style.display = 'none';
        }
        document.getElementById(sectionId).style.display = 'block';
    }
</script>
</body>
</html>

