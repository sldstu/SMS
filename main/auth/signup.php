<?php
require_once '../functions/user.class.php';
session_start();

$signupErr = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    /*$first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];*/
    $role = 'student'; // Default role is student

    $user = new User();
    if ($user->fetch($username)) {
        $signupErr = 'Username already exists. Please choose another.';
    } else {
        if ($user->signup($username, $password, $role, $email)) {
            header("Location: login.php");
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
    <script>
        // JavaScript function to check if passwords match and if the password length is >= 8
        function validateForm() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm-password").value;
            var passwordError = document.getElementById("password-error");
            var confirmPasswordError = document.getElementById("confirm-password-error");
            
            // Check if password is at least 8 characters
            if (password.length < 8) {
                passwordError.textContent = "Password must be at least 8 characters long.";
                return false;
            } else {
                passwordError.textContent = "";
            }

            // Check if passwords match
            if (password !== confirmPassword) {
                confirmPasswordError.textContent = "Passwords do not match.";
                return false;
            } else {
                confirmPasswordError.textContent = "";
            }
            
            return true;
        }
    </script>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow p-4" style="width: 100%; max-width: 500px;">
            <h2 class="text-center mb-4">Sign Up</h2>
            <?php if ($signupErr): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($signupErr) ?></div>
            <?php endif; ?>
            <form action="signup.php" method="post" onsubmit="return validateForm()">
                <!--<div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>-->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small id="password-error" class="text-danger"></small>
                </div>
                <div class="mb-3">
                    <label for="confirm-password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm-password" name="confirm-password" required>
                    <small id="confirm-password-error" class="text-danger"></small>
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
