<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

$query = $conn->prepare("SELECT * FROM sports");
$query->execute();
$sports = $query->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action']) && $input['action'] === 'delete_sport' && isset($input['sport_id'])) {
        $sport_id = $input['sport_id'];

        try {
            $query = $conn->prepare("DELETE FROM sports WHERE sport_id = :sport_id");
            $query->bindParam(':sport_id', $sport_id);
            $query->execute();

            echo json_encode(['success' => true]);
            exit();
        } catch (Exception $e) {
            error_log('Error deleting sport: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error while deleting sport.']);
            exit();
        }
    } elseif (isset($input['action']) && $input['action'] === 'create_sport') {
        $sport_name = trim($input['sport_name']);
        $sport_description = trim($input['sport_description']);

        if (empty($sport_name) || empty($sport_description)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit();
        }

        try {
            $query = $conn->prepare("
                INSERT INTO sports (sport_name, sport_description, teacher_id) 
                VALUES (:sport_name, :sport_description, :teacher_id)
            ");
            $query->bindParam(':sport_name', $sport_name);
            $query->bindParam(':sport_description', $sport_description);
            $query->bindParam(':teacher_id', $_SESSION['user_id']);

            if ($query->execute()) {
                echo json_encode([
                    'success' => true,
                    'sport' => [
                        'sport_id' => $conn->lastInsertId(),
                        'sport_name' => $sport_name,
                        'sport_description' => $sport_description
                    ]
                ]);
                exit();
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error creating sport: ' . $e->getMessage()]);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/SMS/css/style.css">
</head>

<body>
    <div id="sports_section" class="container mt-4">
        <h2 class="mb-4">Sports Management</h2>

        <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
            <input type="text" id="search_bar" class="form-control w-auto" placeholder="Search sports..." onkeyup="searchSport()">
            <button class="btn btn-primary px-4 shadow-sm" onclick="sortTable()">Sort Alphabetically</button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSportModal">
                Create Sport
            </button>
        </div>

        <!-- Sports Table -->
        <div class="table-responsive">
            <table id="sports_table" class="table table-hover align-middle table-bordered rounded-3 overflow-hidden shadow">
                <thead class="table-primary">
                    <tr class="text-center">
                        <th>Sport Name</th>
                        <th>Sport Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sports as $sport): ?>
                        <tr id="sport-row-<?= $sport['sport_id'] ?>">
                            <td class="sport-name"><?= htmlspecialchars($sport['sport_name']) ?></td>
                            <td class="sport-description"><?= htmlspecialchars($sport['sport_description']) ?></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-warning btn-sm edit-sport-btn px-3" data-sport-id="<?= $sport['sport_id'] ?>">Edit</button>
                                    <button class="btn btn-danger btn-sm delete-sport-btn px-3" data-sport-id="<?= $sport['sport_id'] ?>" data-sport-name="<?= htmlspecialchars($sport['sport_name']) ?>">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Sport Modal -->
<div class="modal fade" id="addSportModal" tabindex="-1" aria-labelledby="addSportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSportModalLabel">Add New Sport</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addSportForm" novalidate>
                    <div class="mb-3">
                        <label for="sport_name" class="form-label">Sport Name</label>
                        <input type="text" class="form-control" id="sport_name" name="sport_name" required>
                        <div class="invalid-feedback">
                            Please enter a sport name.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="sport_description" class="form-label">Sport Description</label>
                        <textarea class="form-control" id="sport_description" name="sport_description" rows="3" required></textarea>
                        <div class="invalid-feedback">
                            Please enter a sport description.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="createSportBtn">Add Sport</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Sport Modal -->
<div class="modal fade" id="editSportModal" tabindex="-1" aria-labelledby="editSportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSportModalLabel">Edit Sport</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveChangesBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>


    <!-- Modal for Deleting Sport -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="confirmationModalLabel">Confirm Deletion</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p id="confirmationMessage"></p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
          </div>
        </div>
    </div>

    <script>
        function searchSport() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("search_bar");
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

        function sortTable() {
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

        document.addEventListener('DOMContentLoaded', () => {
            // Attach event listeners to delete buttons
            document.querySelectorAll('.delete-sport-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const sportId = this.dataset.sportId;
                    const sportName = this.dataset.sportName;

                    document.getElementById('confirmationMessage').textContent = `Are you sure you want to delete sport ${sportName}?`;

                    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                    modal.show();

                    document.getElementById('confirmDeleteBtn').onclick = function() {
                        fetch('../MAIN/roles/moderator_/sports_mod.php', {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({
                                action: "delete_sport",
                                sport_id: sportId,
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const sportRow = document.getElementById(`sport-row-${sportId}`);
                                if (sportRow) {
                                    sportRow.remove();
                                }
                                bootstrap.Modal.getInstance(document.getElementById("confirmationModal")).hide(); // Hide the modal after deletion
                            } else {
                                alert(data.message || "Failed to delete sport.");
                            }
                        })
                        .catch(error => {
                            console.error("Error during delete operation:", error);
                            alert("An error occurred while deleting the sport.");
                        });
                    };
                });
            });

            // Attach edit button listeners
            document.querySelectorAll(".edit-sport-btn").forEach(button => {
                button.addEventListener("click", event => {
                    event.preventDefault();
                    const sportId = button.dataset.sportId;

                    fetch(`../MAIN/edit/edit_sport.php?sport_id=${sportId}`)
                        .then(response => response.text())
                        .then(html => {
                            const modalBody = document.querySelector("#editSportModal .modal-body");
                            modalBody.innerHTML = html;
                            new bootstrap.Modal(document.getElementById("editSportModal")).show();
                        })
                        .catch(err => console.error(err));
                });
            });

            // Save changes button logic
            const saveChangesBtn = document.getElementById("saveChangesBtn");
            if (saveChangesBtn) {
                saveChangesBtn.addEventListener("click", () => {
                    const form = document.querySelector("#editSportModal .modal-body form");

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
                    fetch("../MAIN/edit/edit_sport.php", {
                            method: "POST",
                            body: formData,
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                const sportId = formData.get("sport_id");
                                const sportRow = document.querySelector(`#sport-row-${sportId}`);
                                if (sportRow) {
                                    sportRow.querySelector(".sport-name").textContent = formData.get("sport_name");
                                    sportRow.querySelector(".sport-description").textContent = formData.get("sport_description");
                                }
                                const modal = bootstrap.Modal.getInstance(document.getElementById("editSportModal"));
                                modal.hide();
                            } else {
                                alert("Error saving changes: " + (result.message || "Unknown error"));
                            }
                        })
                        .catch(error => console.error("Error:", error));
                });
            }

            // Real-time validation feedback
            document.querySelectorAll("#editSportModal .modal-body input[required]").forEach(input => {
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

            // Add Sport
            document.getElementById("createSportBtn").addEventListener("click", () => {
                const form = document.getElementById("addSportForm");
                const formData = new FormData(form);

                // Reset all error messages and remove previous "is-invalid" classes
                const inputs = form.querySelectorAll("input, textarea");
                inputs.forEach(input => {
                    input.classList.remove("is-invalid");
                    const invalidFeedback = input.nextElementSibling;
                    if (invalidFeedback) {
                        invalidFeedback.style.display = "none";
                    }
                });

                let isValid = true;

                // Check if any required field is empty and display invalid feedback
                form.querySelectorAll("input[required], textarea[required]").forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add("is-invalid");
                        const invalidFeedback = input.nextElementSibling;
                        if (invalidFeedback) {
                            invalidFeedback.style.display = "block";
                        }
                        isValid = false;
                    }
                });

                // If the form is invalid, stop the process
                if (!isValid) return;

                const data = {
                    action: "create_sport",
                    sport_name: formData.get("sport_name"),
                    sport_description: formData.get("sport_description")
                };

                // Send the request to the server
                fetch("../MAIN/roles/moderator_/sports_mod.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(data),
                    })
                    .then((response) => response.json())
                    .then((result) => {
                        if (result.success) {
                            // Reload the page to show the newly added sport
                            location.reload();
                        } else {
                            alert(result.message || "Failed to create sport.");
                        }
                    })
                    .catch((error) => console.error("Error:", error));
            });

            // Real-time validation feedback
            document.querySelectorAll("#addSportModal .modal-body input[required], #addSportModal .modal-body textarea[required]").forEach(input => {
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
        });
</script>
