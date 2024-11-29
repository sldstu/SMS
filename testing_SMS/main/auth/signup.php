<?php
require_once '../functions/user.class.php';
session_start();

$signupErr = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $role = $_POST['role']; // Ensure this is included in the form

    $user = new User();
    if ($user->fetch($username)) {
        $signupErr = 'Username already exists. Please choose another.';
    } else {
        if ($user->signup($username, $password, $role, $first_name, $last_name)) {
            // Fetch user data including the newly inserted datetime_sign_up
            $userData = $user->fetch($username);
            $_SESSION['user_id'] = $userData['user_id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['datetime_sign_up'] = $userData['datetime_sign_up'];
            $_SESSION['datetime_last_online'] = $userData['datetime_last_online'];
            header("Location: ../../SMS/index.php");
            exit();
        } else {
            $signupErr = 'Error during registration. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow p-4" style="width: 100%; max-width: 500px;">
            <h2 class="text-center mb-4">Sign Up</h2>
            <?php if ($signupErr): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($signupErr) ?></div>
            <?php endif; ?>
            <form action="signup.php" method="post">
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role">
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Sign Up</button>
                </div>
                <div class="mt-3 text-center">
                    <a href="login.php">Already have an account? Log in</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>