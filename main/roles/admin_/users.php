<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

$query = $conn->prepare("SELECT * FROM users");
$query->execute();
$users = $query->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action']) && $input['action'] === 'delete_user' && isset($input['user_id'])) {
        $user_id = $input['user_id'];

        try {
            $query = $conn->prepare("DELETE FROM registrations WHERE student_id = :user_id");
            $query->bindParam(':user_id', $user_id);
            $query->execute();

            $query = $conn->prepare("UPDATE sports SET moderator_id = NULL WHERE moderator_id = :user_id");
            $query->bindParam(':user_id', $user_id);
            $query->execute();

            $query = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
            $query->bindParam(':user_id', $user_id);
            if ($query->execute()) {
                echo json_encode(['success' => true]);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete user from the database.']);
                exit();
            }
        } catch (Exception $e) {
            error_log('Error deleting user: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error while deleting user.']);
            exit();
        }
    } elseif (isset($input['action']) && $input['action'] === 'create_user') {
        $username = trim($input['username']);
        $password = password_hash(trim($input['password']), PASSWORD_BCRYPT);
        $first_name = trim($input['first_name']);
        $last_name = trim($input['last_name']);
        $email = trim($input['email']);
        $middle_name = trim($input['middle_name'] ?? '');

        $role = trim($input['role']);

        // Validate input and role
        if (empty($username) || empty($password) || empty($first_name) || empty($last_name) || empty($role)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit();
        }

        // Validate role is one of the allowed types
        $allowed_roles = ['student', 'moderator', 'admin', 'coach', 'facilitator'];
        if (!in_array($role, $allowed_roles)) {
            echo json_encode(['success' => false, 'message' => 'Invalid role specified.']);
            exit();
        }

        // Check if username exists
        $query = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $query->bindParam(':username', $username);
        $query->execute();

        if ($query->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Username already exists.']);
            exit();
        }

        try {
            // Insert new user with role
            $query = $conn->prepare("
            INSERT INTO users (username, password, first_name, middle_name, last_name, email, role, datetime_sign_up) 
            VALUES (:username, :password, :first_name, :middle_name, :last_name, :email, :role, NOW())
        ");

            $query->bindParam(':username', $username);
            $query->bindParam(':password', $password);
            $query->bindParam(':first_name', $first_name);
            $query->bindParam(':middle_name', $middle_name);
            $query->bindParam(':last_name', $last_name);
            $query->bindParam(':email', $email);
            $query->bindParam(':role', $role);

            if ($query->execute()) {
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'user_id' => $conn->lastInsertId(),
                        'username' => $username,
                        'first_name' => $first_name,
                        'middle_name' => $middle_name,
                        'last_name' => $last_name,
                        'email' => $email,
                        'role' => $role,
                        'datetime_sign_up' => date('Y-m-d H:i:s'),
                        'datetime_last_online' => null
                    ]
                ]);
                exit();
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error creating user: ' . $e->getMessage()]);
            exit();
        }
    }
}
?>
<style>
    .user-inactive {
        background-color: #f8f9fa;
        color: #6c757d;
        font-style: italic;
    }
</style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/SMS/css/style.css">
</head>

<body>
    <div id="users_section" class="container mt-4">
        <h2 class="mb-4">User Management</h2>

        <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
            <input type="text" id="search_bar" class="form-control w-auto" placeholder="Search usernames..." onkeyup="searchUser()">
            <div class="filter-container d-flex gap-3 align-items-center">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="show_active" checked>
                    <label class="form-check-label" for="show_active">Active Users</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="show_inactive">
                    <label class="form-check-label" for="show_inactive">Inactive Users</label>
                </div>
                <select id="role_filter" class="form-select w-auto">
                    <option value="">All Roles</option>
                    <option value="student">Student</option>
                    <option value="moderator">Moderator</option>
                    <option value="coach">Coach</option>
                    <option value="facilitator">Facilitator</option>
                    <option value="admin">Admin</option>
                </select>
                <button class="btn btn-primary px-4 shadow-sm" onclick="sortTable()">Sort Alphabetically</button>
            </div>
        </div>



        <!-- Users Table -->
        <div class="table-responsive">
            <table id="users_table" class="table table-hover align-middle table-bordered rounded-3 overflow-hidden shadow">
                <thead class="table-primary">
                    <tr class="text-center">
                        <th>Username</th>
                        <th>Role</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Sign Up Time</th>
                        <th>Last Online</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr id="user-row-<?= $user['user_id'] ?>">
                            <td class="username"><?= htmlspecialchars($user['username']) ?></td>
                            <td class="role"><?= htmlspecialchars($user['role']) ?></td>
                            <td class="first_name"><?= htmlspecialchars($user['first_name']) ?></td>
                            <td class="middle_name"><?= htmlspecialchars($user['middle_name']) ?></td>
                            <td class="last_name"><?= htmlspecialchars($user['last_name']) ?></td>
                            <td class="email"><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['datetime_sign_up']) ?></td>
                            <td><?= htmlspecialchars($user['datetime_last_online']) ?></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <div class="btn-group">
                                        <button class="btn btn-warning btn-sm edit-user-btn px-3" data-user-id="<?= $user['user_id'] ?>">Edit</button>
                                        <button type="button" class="btn btn-warning btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="visually-hidden">Toggle Status</span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item status-action" href="#" data-action="activate" data-user-id="<?= $user['user_id'] ?>">Activate Account</a></li>
                                            <li><a class="dropdown-item status-action" href="#" data-action="deactivate" data-user-id="<?= $user['user_id'] ?>">Deactivate Account</a></li>
                                        </ul>
                                    </div>
                                    <button class="btn btn-danger btn-sm delete-user-btn px-3" data-user-id="<?= $user['user_id'] ?>" data-username="<?= htmlspecialchars($user['username']) ?>">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php include '../MAIN/auth/loads/confirmation.php'; ?>
        </div>
    </div>


    <!-- Modal for editing user -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content ">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Content will be loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveChangesBtn">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <div id="usernameError" class="invalid-feedback">
                                Please enter a unique username.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback">
                                Please enter a password.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                            <div class="invalid-feedback">
                                Please provide the first name.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                            <div class="invalid-feedback">
                                Please provide the last name.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="middle_name" class="form-label">Middle Name (Optional)</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="student">Student</option>
                                <option value="moderator">Moderator</option>
                                <option value="coach">Coach</option>
                                <option value="facilitator">Facilitator</option>
                                <option value="admin">Admin</option>
                            </select>
                            <div class="invalid-feedback">
                                Please specify the role.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="createUserBtn">Add User</button>
                </div>
            </div>
        </div>
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
        // function filterRole() {
        //     var input, filter, table, tr, td, i, txtValue;
        //     input = document.getElementById("role_filter");
        //     filter = input.value.toUpperCase();
        //     table = document.getElementById("users_table");
        //     tr = table.getElementsByTagName("tr");

        //     for (i = 1; i < tr.length; i++) {
        //         td = tr[i].getElementsByTagName("td")[1];
        //         if (td) {
        //             txtValue = td.textContent || td.innerText;
        //             if (filter === "" || txtValue.toUpperCase() === filter) {
        //                 tr[i].style.display = "";
        //             } else {
        //                 tr[i].style.display = "none";
        //             }
        //         }
        //     }
        // }

        function filterUsers() {
            const showActive = document.getElementById('show_active').checked;
            const showInactive = document.getElementById('show_inactive').checked;
            const roleFilter = document.getElementById('role_filter').value.toUpperCase();
            const table = document.getElementById('users_table');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const roleCell = row.getElementsByTagName('td')[1];
                const isInactive = row.classList.contains('user-inactive');

                let showRow = true;

                // Check status filter
                if ((!showActive && !isInactive) || (!showInactive && isInactive)) {
                    showRow = false;
                }

                // Check role filter
                if (roleFilter && roleCell) {
                    const roleText = roleCell.textContent || roleCell.innerText;
                    if (roleText.toUpperCase() !== roleFilter && roleFilter !== '') {
                        showRow = false;
                    }
                }

                row.style.display = showRow ? '' : 'none';
            }
        }
        document.getElementById('show_active').addEventListener('change', filterUsers);
        document.getElementById('show_inactive').addEventListener('change', filterUsers);
        document.getElementById('role_filter').addEventListener('change', filterUsers);

        document.querySelectorAll('.status-action').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.dataset.userId;
                const action = this.dataset.action;

                fetch('../MAIN/roles/admin_/update_user_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            action: action
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const row = document.querySelector(`#user-row-${userId}`);
                            row.classList.toggle('user-inactive', action === 'deactivate');
                            filterUsers(); // Reapply filters after status change
                        }
                    });
            });
        });

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



        document.querySelectorAll('.status-action').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.dataset.userId;
                const action = this.dataset.action;

                fetch('../MAIN/roles/admin_/update_user_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            action: action
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update UI to reflect new status
                            const row = document.querySelector(`#user-row-${userId}`);
                            row.classList.toggle('table-secondary', action === 'deactivate');
                        }
                    });
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Attach event listeners to delete buttons
            document.querySelectorAll('.delete-user-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    const username = this.dataset.username;

                    // Show confirmation modal
                    showConfirmationModal(
                        `Are you sure you want to delete user ${username}?`,
                        () => {
                            fetch("../MAIN/roles/admin_/users.php", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                    },
                                    body: JSON.stringify({
                                        action: "delete_user",
                                        user_id: userId,
                                    }),
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        const userRow = document.getElementById(`user-row-${userId}`);
                                        if (userRow) {
                                            userRow.remove();
                                        }
                                    } else {
                                        alert(data.message || "Failed to delete user.");
                                    }
                                })
                                .catch(error => {
                                    console.error("Error during delete operation:", error);
                                    alert("An error occurred while deleting the user.");
                                });
                        },
                        "btn-danger",
                        "Delete"
                    );
                });
            });

            // Attach edit button listeners
            document.querySelectorAll(".edit-user-btn").forEach(button => {
                button.addEventListener("click", event => {
                    event.preventDefault();
                    const userId = button.dataset.userId;

                    fetch(`../MAIN/edit/edit_user.php?user_id=${userId}`)
                        .then(response => response.text())
                        .then(html => {
                            const modalBody = document.querySelector("#editUserModal .modal-body");
                            modalBody.innerHTML = html;
                            new bootstrap.Modal(document.getElementById("editUserModal")).show();
                        })
                        .catch(err => console.error(err));
                });
            });

            // Save changes button logic
            const saveChangesBtn = document.getElementById("saveChangesBtn");
            if (saveChangesBtn) {
                saveChangesBtn.addEventListener("click", () => {
                    const form = document.querySelector("#editUserModal .modal-body form");

                    form.querySelectorAll(".invalid-feedback").forEach(feedback => {
                        feedback.style.display = "none";
                    });

                    let isValid = true;

                    form.querySelectorAll("input[required]").forEach(input => {
                        if (!input.value.trim()) {
                            isValid = false;
                            const invalidFeedback = input.nextElementSibling;
                            if (invalidFeedback) {
                                invalidFeedback.style.display = "block";
                            }
                            input.classList.add("is-invalid");
                        } else {
                            input.classList.remove("is-invalid");
                        }
                    });

                    if (!isValid) return;

                    const formData = new FormData(form);
                    fetch("../MAIN/edit/edit_user.php", {
                            method: "POST",
                            body: formData,
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                const userId = formData.get("user_id");
                                const userRow = document.querySelector(`#user-row-${userId}`);
                                if (userRow) {
                                    userRow.querySelector(".username").textContent = formData.get("username");
                                    userRow.querySelector(".role").textContent = formData.get("role");
                                    userRow.querySelector(".first_name").textContent = formData.get("first_name");
                                    userRow.querySelector(".middle_name").textContent = formData.get("middle_name");
                                    userRow.querySelector(".last_name").textContent = formData.get("last_name");
                                    userRow.querySelector(".email").textContent = formData.get("email");
                                }
                                const modal = bootstrap.Modal.getInstance(document.getElementById("editUserModal"));
                                modal.hide();
                            } else {
                                alert("Error saving changes: " + (result.message || "Unknown error"));
                            }
                        })
                        .catch(error => console.error("Error:", error));
                });
            }


            // Real-time validation feedback
            document.querySelectorAll("#editUserModal .modal-body input[required]").forEach(input => {
                input.addEventListener("input", function() {
                    if (this.value.trim()) {
                        this.classList.remove("is-invalid");
                        const invalidFeedback = this.nextElementSibling;
                        if (invalidFeedback) {
                            invalidFeedback.style.display = "none";
                        }
                    }
                });
            });

            document.getElementById("createUserBtn").addEventListener("click", () => {
                const form = document.getElementById("addUserForm");
                const formData = new FormData(form);

                // Reset all error messages and remove previous "is-invalid" classes
                const inputs = form.querySelectorAll("input, select");
                inputs.forEach(input => {
                    input.classList.remove("is-invalid");
                    const invalidFeedback = input.nextElementSibling;
                    if (invalidFeedback) {
                        invalidFeedback.style.display = "none"; // Hide the error messages
                    }
                });

                let isValid = true;

                // Check if any required field is empty and display invalid feedback
                form.querySelectorAll("input[required], select[required]").forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add("is-invalid"); // Add the red border
                        const invalidFeedback = input.nextElementSibling;
                        if (invalidFeedback) {
                            invalidFeedback.style.display = "block"; // Show the error message
                        }
                        isValid = false;
                    }
                });

                // If the form is invalid, stop the process
                if (!isValid) return;

                const data = {
                    action: "create_user",
                    username: formData.get("username"),
                    password: formData.get("password"),
                    first_name: formData.get("first_name"),
                    last_name: formData.get("last_name"),
                    role: formData.get("role"),
                };

                // Send the request to the server
                fetch("../MAIN/roles/admin_/users.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(data),
                    })
                    .then((response) => response.json())
                    .then((result) => {
                        if (result.success) {
                            const user = result.user;
                            const tbody = document.querySelector("#users_table tbody");

                            tbody.insertAdjacentHTML(
                                "beforeend",
                                `<tr id="user-row-${user.user_id}">
                        <td>${user.username}</td>
                        <td>${user.role}</td>
                        <td>${user.first_name}</td>
                        <td>${user.last_name}</td>
                        <td>${user.datetime_sign_up}</td>
                        <td>${user.datetime_last_online || ""}</td>
                        <td class="text-center">
                            <button class="btn btn-warning btn-sm edit-user-btn px-3" data-user-id="${user.user_id}">Edit</button>
                            <button class="btn btn-danger btn-sm delete-user-btn px-3" data-user-id="${user.user_id}" data-username="${user.username}">Delete</button>
                        </td>
                    </tr>`
                            );

                            // Reset the form and hide the modal
                            form.reset();
                            bootstrap.Modal.getInstance(document.getElementById("addUserModal")).hide();
                        } else {
                            // Handle error for existing username
                            if (result.message === "Username already exists. Please choose another.") {
                                const usernameInput = document.getElementById("username");
                                const usernameError = document.getElementById("usernameError");
                                usernameError.textContent = result.message;
                                usernameInput.classList.add("is-invalid"); // Highlight input with error
                                usernameError.style.display = "block"; // Show error message
                            } else {
                                alert(result.message || "Failed to create user.");
                            }
                        }
                    })
                    .catch((error) => console.error("Error:", error));
            });

            // Real-time validation feedback
            document.querySelectorAll("#addUserModal .modal-body input[required]").forEach(input => {
                input.addEventListener("input", function() {
                    if (this.value.trim()) {
                        this.classList.remove("is-invalid");
                        const invalidFeedback = this.nextElementSibling;
                        if (invalidFeedback) {
                            invalidFeedback.style.display = "none"; // Hide error message
                        }
                    }
                });
            });

            // Specifically handle username validation (to show the message "Username already exists.")
            document.getElementById("username").addEventListener("input", function() {
                const usernameError = document.getElementById("usernameError");
                usernameError.style.display = "none"; // Hide the "Username already exists." message when typing
            });

        });
    </script>

</body>
<?php include_once '../MAIN/includes/_footer.php'; ?>

</html>