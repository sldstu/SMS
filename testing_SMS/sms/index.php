<?php
session_start();
ob_start(); // Start output buffering to prevent premature output

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../MAIN/auth/login.php');
    exit();
}

$base_dir = __DIR__;
$allowed_pages = [
    'admin_dashboard' => '../MAIN/roles/admin_/dashboard.php',
    'admin_users' => '../MAIN/roles/admin_/users.php',
    'admin_sports' => '../MAIN/roles/admin_/sports.php',
    'admin_events' => '../MAIN/roles/admin_/events.php',
    'admin_course_section' => '../MAIN/roles/admin_/course_section.php',
    '404' => 'MAIN/roles/guest/404.php',
];

// Determine the current page based on the URL parameter
$page = $_GET['page'] ?? ($_SESSION['last_page'] ?? 'admin_dashboard');

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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrapper">
        <aside id="sidebar" class="js-sidebar">
            <!-- Content For Sidebar -->
            <div class="h-100">
                <div class="sidebar-logo">
                    <a href="#">
                        <?php
                        // session_start();
                        echo "Welcome, ", $_SESSION['username'];
                        echo "<br>";
                        echo $_SESSION['role'];
                        ?>
                    </a>
                </div>
                <ul class="sidebar-nav">
                    <li class="sidebar-header">
                        Admin Tabs
                    </li>

                    <li class="sidebar-item">
                        <a href="?page=admin_dashboard" class="sidebar-link ajax-link">
                            <i class="bi bi-grid-fill pe-2"></i>
                            Dashboard
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="?page=admin_users" class="sidebar-link ajax-link">
                            <i class="fa-solid fa-user pe-2"></i>
                            Users
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="?page=admin_sports" class="sidebar-link ajax-link">
                            <i class="fa-solid fa-medal pe-2"></i>
                            Sports
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="?page=admin_events" class="sidebar-link ajax-link">
                            <i class="fa-solid fa-calendar-days pe-2"></i>
                            Events
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="?page=admin_course_section" class="sidebar-link ajax-link">
                            <i class="bi bi-mortarboard-fill pe-2"></i>
                            Course and Sections
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
        <div class="main">
            <nav class="navbar navbar-expand px-3 border-bottom">
                <button class="btn" id="sidebar-toggle" type="button">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Search Bar -->
                <form class="d-flex ms-auto me-3" role="search">
                    <input class="form-control me-2" type="search" placeholder="Search..." aria-label="Search">
                    <button class="btn btn-outline-primary" type="submit"><i
                            class="fa-solid fa-magnifying-glass"></i></button>
                </form>


                <a href="#" class="theme-toggle">
                    <i class="fa-regular fa-moon"></i>
                    <i class="fa-regular fa-sun"></i>
                </a>

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
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Here goes the content or PHP code files -->
            <main class="content px-3 py-2" id="content">
                <?php
                // Include the resolved PHP file
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://kit.fontawesome.com/ae360af17e.js" crossorigin="anonymous"></script>
    <script src="js/script.js"></script>
    <script src="js/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../main/roles/admin_/js/admin.js"></script>
    <script>
        // $(document).ready(function() {
        //     // Intercept clicks on links with the class 'ajax-link'
        //     $('.ajax-link').on('click', function(e) {
        //         e.preventDefault(); // Prevent default anchor behavior (page refresh)

        //         const url = $(this).attr('href') + '&ajax=true'; // Add an 'ajax=true' query parameter

        //         // Fetch the content dynamically
        //         $.ajax({
        //             url: url,
        //             method: 'GET',
        //             success: function(response) {
        //                 $('#content').html(response); // Replace the #content container with the new content
        //                 window.history.pushState(null, '', $(this).attr('href')); // Update the URL in the browser
        //             },
        //             error: function() {
        //                 $('#content').html('<p>Error loading content. Please try again later.</p>');
        //             }
        //         });
        //     });

        //     // Handle browser navigation (back/forward buttons)
        //     $(window).on('popstate', function() {
        //         const url = window.location.href + '&ajax=true';
        //         $.ajax({
        //             url: url,
        //             method: 'GET',
        //             success: function(response) {
        //                 $('#content').html(response);
        //             },
        //             error: function() {
        //                 $('#content').html('<p>Error loading content. Please try again later.</p>');
        //             }
        //         });
        //     });
        // });
    </script>

</body>

</html>