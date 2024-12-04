<?php
session_start();
ob_start(); // Start output buffering to prevent premature output

if (!isset($_SESSION['role'])) {
    header('Location: ../MAIN/auth/login.php');
    exit();
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Define allowed pages and sidebar items based on role
$base_dir = __DIR__;
$allowed_pages = [];
$sidebar_items = [];

// Dynamic configuration for allowed pages and sidebar items based on role
if ($role === 'admin') {
    $allowed_pages = [
        'admin_dashboard' => '../MAIN/roles/admin_/dashboard.php',
        'admin_users' => '../MAIN/roles/admin_/users.php',
        'admin_sports' => '../MAIN/roles/admin_/sports.php',
        'admin_events' => '../MAIN/roles/admin_/events.php',
        'admin_course_section' => '../MAIN/roles/admin_/course_section.php',
        'admin_registration' => '../MAIN/roles/admin_/registration.php', // New page
        '404' => 'MAIN/roles/guest/404.php',
    ];
    
    $sidebar_items = [
        ['name' => 'Dashboard', 'icon' => 'bi bi-grid-fill', 'page' => 'admin_dashboard'],
        ['name' => 'Users', 'icon' => 'fa-solid fa-user', 'page' => 'admin_users'],
        ['name' => 'Sports', 'icon' => 'fa-solid fa-medal', 'page' => 'admin_sports'],
        ['name' => 'Events', 'icon' => 'fa-solid fa-calendar-days', 'page' => 'admin_events'],
        ['name' => 'Course and Sections', 'icon' => 'bi bi-mortarboard-fill', 'page' => 'admin_course_section'],
        ['name' => 'Registration', 'icon' => 'fa-solid fa-clipboard-list', 'page' => 'admin_registration'], // New item
    ];
    
} elseif ($role === 'teacher') {
    $allowed_pages = [
        'mod_dashboard' => '../MAIN/roles/moderator_/dashboard_mod.php',
        'mod_events' => '../MAIN/roles/moderator_/events_mod.php',
        'mod_sports' => '../MAIN/roles/moderator_/sports_mod.php',
        'mod_studReg' => '../MAIN/roles/moderator_/studReg_mod.php',
        '404' => 'MAIN/roles/guest/404.php',
    ];
    $sidebar_items = [
        ['name' => 'Dashboard', 'icon' => 'bi bi-grid-fill', 'page' => 'mod_dashboard'],
        ['name' => 'Events', 'icon' => 'fa-solid fa-calendar-days', 'page' => 'mod_events'],
        ['name' => 'Sports', 'icon' => 'fa-solid fa-medal', 'page' => 'mod_sports'],
        ['name' => 'Student Registration', 'icon' => 'fa-solid fa-user', 'page' => 'mod_studReg'],
    ];
} elseif ($role === 'student') {
    $allowed_pages = [
        'student_events' => '../MAIN/roles/student_/events_stud.php',
        'student_regSports' => '../MAIN/roles/student_/regSports.php',
        'student_sports' => '../MAIN/roles/student_/sports_stud.php',
        '404' => 'MAIN/roles/guest/404.php',
    ];
    $sidebar_items = [
        ['name' => 'Events', 'icon' => 'fa-solid fa-calendar-days', 'page' => 'student_events'],
        ['name' => 'Registered Sports', 'icon' => 'fa-solid fa-user-check', 'page' => 'student_regSports'],
        ['name' => 'Sports', 'icon' => 'fa-solid fa-medal', 'page' => 'student_sports'],
    ];
} else {
    header('Location: ../MAIN/auth/login.php');
    exit();
}

// Determine the current page based on the URL parameter or session
$page = $_GET['page'] ?? ($_SESSION['last_page'] ?? array_key_first($allowed_pages));

// Save the current page in the session to persist between refreshes
$_SESSION['last_page'] = $page;

$file_to_include = $allowed_pages[$page] ?? $allowed_pages['404'];

// Handle AJAX requests
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    if (file_exists($file_to_include)) {
        include_once $file_to_include;
    } else {
        echo "Error: File not found.";
    }
    exit(); // Stop further execution for AJAX requests
}

ob_end_flush(); // Flush the buffered output after headers
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($role) ?> Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside id="sidebar" class="js-sidebar">
            <div class="h-100">
                <div class="sidebar-logo">
                    <a href="#">
                        Welcome, <?= htmlspecialchars($username) ?><br>
                        Role: <?= htmlspecialchars($role) ?>
                    </a>
                </div>
                <ul class="sidebar-nav">
                    <li class="sidebar-header"><?= ucfirst($role) ?> Tabs</li>
                    <?php foreach ($sidebar_items as $item): ?>
                        <li class="sidebar-item">
                            <a href="?page=<?= $item['page'] ?>" class="sidebar-link ajax-link">
                                <i class="<?= $item['icon'] ?> pe-2"></i>
                                <?= $item['name'] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main">
            <nav class="navbar navbar-expand px-3 border-bottom">
                <button class="btn" id="sidebar-toggle" type="button">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Search Bar -->
                <form class="d-flex ms-auto me-3" role="search">
                    <input class="form-control me-2" type="search" placeholder="Search..." aria-label="Search">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>

                <div class="navbar-collapse navbar">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" class="nav-icon pe-md-0">
                                <img src="image/profile.jpg" class="avatar img-fluid rounded">
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item">Profile</a>
                                <a href="#" class="dropdown-item">Setting</a>
                                <a href="../MAIN/auth/logout.php" class="dropdown-item">Logout</a>
                            </div>
                            <a href="../MAIN/auth/logout.php" class="dropdown-item">Logout</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Content Section -->
            <main class="content px-3 py-2" id="content">
                <?php
                if (file_exists($file_to_include)) {
                    include_once $file_to_include;
                } else {
                    echo "Error: File not found.";
                }
                ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Intercept sidebar link clicks for AJAX loading
        $(document).on('click', '.ajax-link', function (e) {
            e.preventDefault();
            const url = $(this).attr('href') + '&ajax=true';
            $.get(url, function (data) {
                $('#content').html(data);
                history.pushState(null, '', $(this).attr('href'));
            });
        });

        // Handle browser back/forward navigation
        $(window).on('popstate', function () {
            const url = location.href + '&ajax=true';
            $.get(url, function (data) {
                $('#content').html(data);
            });
        });
    </script>
</body>

</html>
