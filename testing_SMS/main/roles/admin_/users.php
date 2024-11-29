<?php
// session_start();
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

    // if (isset($data['action']) && $data['action'] === 'delete_user' && isset($input['user_id'])) {
    //     $input = json_decode(file_get_contents('php://input'), true);
    //     $user_id = $data['user_id'];
    //     if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //         if (isset($_POST['delete_sport'])) {
    //             $query = $conn->prepare("DELETE FROM registrations WHERE student_id = :user_id");
    //             $query->bindParam(':user_id', $user_id);
    //             $query->execute();

    //             $query = $conn->prepare("UPDATE sports SET teacher_id = NULL WHERE teacher_id = :user_id");
    //             $query->bindParam(':user_id', $user_id);
    //             $query->execute();

    //             $query = $conn->prepare("DELETE FROM users WHERE user_id = :user_id");
    //             $query->bindParam(':user_id', $user_id);
    //             $query->execute();
    //             exit();
    //         }
    //     }
    // }
    // exit();

    // Handle delete request
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action']) && $input['action'] === 'delete_user' && isset($input['user_id'])) {
        $user_id = $input['user_id'];

        try {
            // Delete related records
            $query = $conn->prepare("DELETE FROM registrations WHERE student_id = :user_id");
            $query->bindParam(':user_id', $user_id);
            $query->execute();

            $query = $conn->prepare("UPDATE sports SET teacher_id = NULL WHERE teacher_id = :user_id");
            $query->bindParam(':user_id', $user_id);
            $query->execute();

            // Delete user
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
    }
}

?>

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
            <select id="role_filter" class="form-select w-auto" onchange="filterRole()">
                <option value="">All Roles</option>
                <option value="student">Student</option>
                <option value="teacher">Moderator</option>
                <option value="admin">Admin</option>
            </select>
            <button class="btn btn-primary px-4 shadow-sm" onclick="sortTable()">Sort Alphabetically</button>
        </div>


        <!-- Users Table -->
        <div class="table-responsive">
        <table id="users_table" class="table table-hover align-middle table-bordered rounded-3 overflow-hidden shadow">
            <thead class="table-primary">
                <tr class="text-center">
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
                    <tr id="user-row-<?= $user['user_id'] ?>">
                        <td class="username"><?= htmlspecialchars($user['username']) ?></td>
                        <td class="role"><?= htmlspecialchars($user['role']) ?></td>
                        <td class="first_name"><?= htmlspecialchars($user['first_name']) ?></td>
                        <td class="last_name"><?= htmlspecialchars($user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['datetime_sign_up']) ?></td>
                        <td><?= htmlspecialchars($user['datetime_last_online']) ?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-warning btn-sm edit-user-btn px-3" data-user-id="<?= $user['user_id'] ?>">Edit</button>
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

        document.addEventListener('DOMContentLoaded', () => {
            // Attach event listeners to delete buttons
            document.querySelectorAll('.delete-user-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    const username = this.dataset.username;

                    console.log(`Delete button clicked for User ID: ${userId}, Username: ${username}`); // Debugging

                    // Show the confirmation modal
                    showConfirmationModal(`Are you sure you want to delete the user "${username}"?`, () => {
                        console.log(`User ${userId} confirmed for deletion`); // Debugging

                        // Callback for confirmed action
                        fetch('../MAIN/roles/admin_/users.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    action: 'delete_user',
                                    user_id: userId
                                })
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('Server response:', data); // Debugging
                                if (data.success) {
                                    // Remove user row dynamically
                                    const userRow = document.getElementById(`user-row-${userId}`);
                                    if (userRow) {
                                        userRow.remove();
                                        console.log(`User ${userId} removed from the table successfully.`); // Debugging
                                    }
                                } else {
                                    alert(data.message || 'Failed to delete user.');
                                }
                            })
                            .catch(error => {
                                console.error('Error during delete operation:', error); // Debugging
                                alert('An error occurred while deleting the user. Please check the console for details.');
                            });
                    }, 'btn-danger', 'Delete');
                });
            });
        });

        document.querySelectorAll('.edit-user-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const userId = this.dataset.userId;
                fetch(`../MAIN/edit/edit_user.php?user_id=${userId}`)
                    .then(response => response.text())
                    .then(html => {
                        const modalBody = document.querySelector('#editUserModal .modal-body');
                        modalBody.innerHTML = html;
                        // Show the modal
                        new bootstrap.Modal(document.getElementById('editUserModal')).show();
                    })
                    .catch(err => console.error(err));
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Save Changes Button Logic
            document.getElementById('saveChangesBtn').addEventListener('click', function() {
                // Get the form inside the modal
                const form = document.querySelector('#editUserModal .modal-body form');

                // Clear previous invalid feedback messages
                form.querySelectorAll('.invalid-feedback').forEach(feedback => {
                    feedback.style.display = 'none';
                });

                let isValid = true;

                // Validate each required input field
                form.querySelectorAll('input[required]').forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        const invalidFeedback = input.nextElementSibling;
                        if (invalidFeedback) {
                            invalidFeedback.style.display = 'block'; // Show invalid feedback
                        }
                        input.classList.add('is-invalid'); // Add invalid class
                    } else {
                        input.classList.remove('is-invalid'); // Remove invalid class if corrected
                    }
                });

                // If the form is invalid, halt execution
                if (!isValid) {
                    return;
                }

                // Collect form data
                const formData = new FormData(form);

                // Proceed with AJAX request
                fetch('../MAIN/edit/edit_user.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            // Update the table dynamically
                            const userId = formData.get('user_id');
                            const userRow = document.querySelector(`#user-row-${userId}`);
                            if (userRow) {
                                userRow.querySelector('.username').textContent = formData.get('username');
                                userRow.querySelector('.role').textContent = formData.get('role');
                                userRow.querySelector('.first_name').textContent = formData.get('first_name');
                                userRow.querySelector('.last_name').textContent = formData.get('last_name');
                            }

                            // Hide the modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                            modal.hide();
                        } else {
                            alert('Error saving changes: ' + (result.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });

            // Add real-time validation feedback
            document.querySelectorAll('#editUserModal .modal-body input[required]').forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('is-invalid'); // Remove invalid class if corrected
                        const invalidFeedback = this.nextElementSibling;
                        if (invalidFeedback) {
                            invalidFeedback.style.display = 'none'; // Hide invalid feedback
                        }
                    }
                });
            });
        });
    </script>

</body>
<?php include_once '../MAIN/includes/_footer.php';?>
</html>