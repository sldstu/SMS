<?php

session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

// Fetch all users
$query = $conn->prepare("SELECT * FROM users");
$query->execute();
$users = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch all sports
$query = $conn->prepare("SELECT * FROM sports");
$query->execute();
$sports = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch all events
$query = $conn->prepare("SELECT * FROM events");
$query->execute();
$events = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch all courses
$query = $conn->prepare("SELECT * FROM courses");
$query->execute();
$courses = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch all sections
$query = $conn->prepare("SELECT s.*, c.course_name FROM sections s JOIN courses c ON s.course_id = c.course_id");
$query->execute();
$sections = $query->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        // Update sports and events to set teacher_id to NULL before deleting the user
        $query = $conn->prepare("UPDATE sports SET teacher_id = NULL WHERE teacher_id = :teacher_id");
        $query->bindParam(':teacher_id', $user_id);
        $query->execute();
        $query = $conn->prepare("UPDATE events SET teacher_id = NULL WHERE teacher_id = :teacher_id");
        $query->bindParam(':teacher_id', $user_id);
        $query->execute();
        $query = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
        $query->bindParam(':user_id', $user_id);
        $query->execute();
        header("Refresh:0");
    } elseif (isset($_POST['delete_sport'])) {
        $sport_id = $_POST['sport_id'];
        $query = $conn->prepare("DELETE FROM sports WHERE sport_id = :sport_id");
        $query->bindParam(':sport_id', $sport_id);
        $query->execute();
        header('Location: success_page.php?message=Sport deleted successfully!');
        exit();
    } elseif (isset($_POST['delete_event'])) {
        $event_id = $_POST['event_id'];
        $query = $conn->prepare("DELETE FROM events WHERE event_id = :event_id");
        $query->bindParam(':event_id', $event_id);
        $query->execute();
        header('Location: success_page.php?message=Event deleted successfully!');
        exit();
    } elseif (isset($_POST['delete_course'])) {
        $course_id = $_POST['course_id'];
        $query = $conn->prepare("DELETE FROM courses WHERE course_id = :course_id");
        $query->bindParam(':course_id', $course_id);
        $query->execute();
        header('Location: success_page.php?message=Course deleted successfully!');
        exit();
    } elseif (isset($_POST['delete_section'])) {
        $section_id = $_POST['section_id'];
        $query = $conn->prepare("DELETE FROM sections WHERE section_id = :section_id");
        $query->bindParam(':section_id', $section_id);
        $query->execute();
        header('Location: success_page.php?message=Section deleted successfully!');
        exit();
    } elseif (isset($_POST['add_sport'])) {
        $sport_name = $_POST['sport_name'];
        $query = $conn->prepare("INSERT INTO sports (sport_name) VALUES (:sport_name)");
        $query->bindParam(':sport_name', $sport_name);
        $query->execute();
        header('Location: success_page.php?message=Sport added successfully!');
        exit();
    } elseif (isset($_POST['add_event'])) {
        $event_name = $_POST['event_name'];
        $event_date = $_POST['event_date'];
        $query = $conn->prepare("INSERT INTO events (event_name, event_date) VALUES (:event_name, :event_date)");
        $query->bindParam(':event_name', $event_name);
        $query->bindParam(':event_date', $event_date);
        $query->execute();
        header('Location: success_page.php?message=Event added successfully!');
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
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h1>Welcome, <?= $_SESSION['first_name'] ?></h1>

    <div class="dashboard-menu">
        <button onclick="showSection('users_section')">Users</button>
        <button onclick="showSection('sports_section')">Sports</button>
        <button onclick="showSection('events_section')">Events</button>
        <button onclick="showSection('courses_sections_section')">Courses and Sections</button>
    </div>

    <div id="users_section" class="dashboard-section" style="display:none;">
        <h2>All Users</h2>
        <input type="text" id="search_bar" onkeyup="searchUser()" placeholder="Search for usernames..">
        <select id="role_filter" onchange="filterRole()">
            <option value="">All Roles</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
            <option value="admin">Admin</option>
        </select>
        <button onclick="sortTable()">Sort Alphabetically</button>
        <table id="users_table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Sign Up Time</th>
                    <th>Last Online</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['username'] ?></td>
                        <td><?= $user['role'] ?></td>
                        <td><?= $user['first_name'] ?></td>
                        <td><?= $user['last_name'] ?></td>
                        <td><?= $user['datetime_sign_up'] ?></td>
                        <td><?= $user['datetime_last_online'] ?></td>
                        <td>
                            <form action="edit_user.php" method="get" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <button type="submit">Edit</button>
                            </form>
                            <form action="admin_dashboard.php" method="post" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <button type="submit" name="delete_user">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function searchUser() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("search_bar");
            filter = input.value.toUpperCase();
            table = document.getElementById("users_table");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function filterRole() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("role_filter");
            filter = input.value.toUpperCase();
            table = document.getElementById("users_table");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[1];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (filter === "" || txtValue.toUpperCase() === filter) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function sortTable() {
            var table, rows, switching, i, x, y, shouldSwitch;
            table = document.getElementById("users_table");
            switching = true;
            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("td")[0];
                    y = rows[i + 1].getElementsByTagName("td")[0];
                    if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                }
            }
        }
    </script>



    <!-- Sports Section -->
    <div id="sports_section" class="dashboard-section" style="display:none;">
        <h2>All Sports</h2>
        <input type="text" id="search_sport" onkeyup="searchSport()" placeholder="Search for sports..">
        <button onclick="sortSportsTable()">Sort Alphabetically</button>
        <table id="sports_table">
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
                            <form action="edit_sport_admin.php" method="get" style="display:inline;">
                                <input type="hidden" name="sport_id" value="<?= $sport['sport_id'] ?>">
                                <button type="submit">Edit</button>
                            </form>
                            <form action="admin_dashboard.php" method="post" style="display:inline;">
                                <input type="hidden" name="sport_id" value="<?= $sport['sport_id'] ?>">
                                <button type="submit" name="delete_sport">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form action="admin_dashboard.php" method="post">
            <h3>Add New Sport</h3>
            <label for="sport_name">Sport Name</label>
            <input type="text" id="sport_name" name="sport_name" required>
            <button type="submit" name="add_sport">Add Sport</button>
        </form>
    </div>

    <script>
        function searchSport() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("search_sport");
            filter = input.value.toUpperCase();
            table = document.getElementById("sports_table");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function sortSportsTable() {
            var table, rows, switching, i, x, y, shouldSwitch;
            table = document.getElementById("sports_table");
            switching = true;
            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("td")[0];
                    y = rows[i + 1].getElementsByTagName("td")[0];
                    if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                }
            }
        }
    </script>



    <div id="events_section" class="dashboard-section" style="display:none;">
        <h2>All Events</h2>
        <input type="text" id="search_event_bar" onkeyup="searchEvent()" placeholder="Search for events..">
        <select id="date_filter" onchange="filterEventsByDate()">
            <option value="">All Dates</option>
            <!-- Add specific date options as needed -->
        </select>
        <table id="events_table">
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
                            <form action="edit_event_admin.php" method="get" style="display:inline;">
                                <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                <button type="submit">Edit</button>
                            </form>
                            <form action="admin_dashboard.php" method="post" style="display:inline;">
                                <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                <button type="submit" name="delete_event">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form action="admin_dashboard.php" method="post">
            <h3>Add New Event</h3>
            <label for="event_name">Event Name</label>
            <input type="text" id="event_name" name="event_name" required>
            <label for="event_date">Event Date</label>
            <input type="date" id="event_date" name="event_date" required>
            <button type="submit" name="add_event">Add Event</button>
        </form>
    </div>

    <script>
        // Search Sports
        function searchSport() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("search_sport_bar");
            filter = input.value.toUpperCase();
            table = document.getElementById("sports_table");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        // Filter Sports Alphabetically
        function filterSportsAlphabetically() {
            var table, rows, switching, i, x, y, shouldSwitch;
            table = document.getElementById("sports_table");
            switching = true;
            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("td")[0];
                    y = rows[i + 1].getElementsByTagName("td")[0];
                    if (document.getElementById('sport_filter').value === "ascending" && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    } else if (document.getElementById('sport_filter').value === "descending" && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                }
            }
        }

        // Search Events
        function searchEvent() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("search_event_bar");
            filter = input.value.toUpperCase();
            table = document.getElementById("events_table");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        // Filter Events by Date
        function filterEventsByDate() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("date_filter");
            filter = input.value;
            table = document.getElementById("events_table");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[1];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>




    <div id="courses_sections_section" class="dashboard-section" style="display:none;">
        <h2>All Courses and Sections</h2>
        <input type="text" id="search_course_bar" onkeyup="searchCourse()" placeholder="Search for courses or sections..">
        <table id="courses_table">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Sections</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?= $course['course_name'] ?></td>
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
                            <form action="edit_course_section_admin.php" method="get" style="display:inline;">
                                <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                                <button type="submit">Edit</button>
                            </form>
                            <form action="admin_dashboard.php" method="post" style="display:inline;">
                                <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                                <button type="submit" name="delete_course">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form action="admin_dashboard.php" method="post">
            <h3>Add New Course</h3>
            <label for="course_name">Course Name</label>
            <input type="text" id="course_name" name="course_name" required>
            <button type="submit" name="add_course">Add Course</button>
        </form>

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
</body>

</html>