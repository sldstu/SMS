<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['action'])) {
        echo json_encode(['success' => false, 'message' => 'No action specified.']);
        exit();
    }

    $action = $input['action'];

    // Add Sport
    if (isset($input['action']) && $input['action'] === 'create_sport') {
      $sport_name = trim($input['sport_name']);
  
      // Validate input
      if (empty($sport_name)) {
          echo json_encode(['success' => false, 'message' => 'Sport name is required.']);
          exit();
      }
  
      // Check if sport name already exists
      $query = $conn->prepare("SELECT * FROM sports WHERE sport_name = :sport_name");
      $query->bindParam(':sport_name', $sport_name);
      $query->execute();
  
      if ($query->rowCount() > 0) {
          echo json_encode(['success' => false, 'message' => 'Sport name already exists.']);
          exit();
      }
  
      try {
          // Insert new sport
          $query = $conn->prepare("INSERT INTO sports (sport_name) VALUES (:sport_name)");
          $query->bindParam(':sport_name', $sport_name);
  
          if ($query->execute()) {
              echo json_encode([
                  'success' => true,
                  'sport' => [
                      'sport_id' => $conn->lastInsertId(),
                      'sport_name' => $sport_name
                  ]
              ]);
              exit();
          }
      } catch (Exception $e) {
          echo json_encode(['success' => false, 'message' => 'Error creating sport: ' . $e->getMessage()]);
          exit();
      }
  }
  
    // Edit Sport
    if ($action === 'edit_sport') {
        if (!isset($input['sport_id'], $input['sport_name']) || empty(trim($input['sport_name']))) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
            exit();
        }

        $sport_id = $input['sport_id'];
        $sport_name = trim($input['sport_name']);

        try {
            $query = $conn->prepare("UPDATE sports SET sport_name = :sport_name WHERE sport_id = :sport_id");
            $query->bindParam(':sport_name', $sport_name);
            $query->bindParam(':sport_id', $sport_id, PDO::PARAM_INT);
            $query->execute();

            echo json_encode(['success' => true, 'sport_name' => $sport_name]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to update sport: ' . $e->getMessage()]);
        }
        exit();
    }

// Delete Sport
if ($action === 'delete_sport') {
  if (!isset($input['sport_id']) || empty($input['sport_id'])) {
      echo json_encode(['success' => false, 'message' => 'Sport ID is required.']);
      exit();
  }

  $sport_id = $input['sport_id'];

  // Check if there are any registrations for the sport
  $checkRegistrationsQuery = $conn->prepare("SELECT COUNT(*) FROM registrations WHERE sport_id = :sport_id");
  $checkRegistrationsQuery->bindParam(':sport_id', $sport_id, PDO::PARAM_INT);
  $checkRegistrationsQuery->execute();
  $registrationsCount = $checkRegistrationsQuery->fetchColumn();

  if ($registrationsCount > 0) {
      echo json_encode([
          'success' => false,
          'message' => 'Cannot delete this sport because there are still students registered for it.'
      ]);
      exit();
  }

  try {
      // Delete the sport
      $query = $conn->prepare("DELETE FROM sports WHERE sport_id = :sport_id");
      $query->bindParam(':sport_id', $sport_id, PDO::PARAM_INT);
      $query->execute();

      echo json_encode(['success' => true, 'message' => 'Sport deleted successfully.']);
  } catch (Exception $e) {
      echo json_encode(['success' => false, 'message' => 'Failed to delete sport: ' . $e->getMessage()]);
  }
  exit();
}
}

// Fetch sports for the page
$query = $conn->prepare("SELECT * FROM sports ORDER BY sport_name ASC");
$query->execute();
$sports = $query->fetchAll(PDO::FETCH_ASSOC);
?>


<div class="container mt-5">
  <h2 class="mb-4">Manage Sports</h2>

  <!-- Search and Sort -->
  <div class="row mb-3">
    <div class="col-md-6">
      <input type="text" id="search_sport" class="form-control" onkeyup="searchSport()" placeholder="Search for sports...">
    </div>
    <div class="col-md-6 text-end">
      <button class="btn btn-primary" onclick="sortSportsTable()">Sort Alphabetically</button>
      <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSportModal">
    Add Sport
</button>

    </div>
    
  </div>

  <!-- Sports Table -->
  <div class="table-responsive">
    <table id="sports_table" class="table table-hover align-middle table-bordered rounded-3 overflow-hidden shadow">
      <thead class="table-primary">
        <tr class="text-center">
          <th>Sport Name</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sports as $sport): ?>
          <tr id="sport-row-<?= $sport['sport_id'] ?>">
            <td class="sport-name"><?= htmlspecialchars($sport['sport_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td class="text-center">
              <!-- Edit Sport Button -->
              <button type="button" class="btn btn-warning btn-sm edit-sport-btn px-3"
                data-sport-id="<?= $sport['sport_id'] ?>"
                data-sport-name="<?= htmlspecialchars($sport['sport_name'], ENT_QUOTES, 'UTF-8') ?>">
                Edit
              </button>
              <!-- Delete Sport Button -->
              <button type="button" class="btn btn-danger btn-sm delete-sport-btn px-3"
                data-sport-id="<?= $sport['sport_id'] ?>"
                data-sport-name="<?= $sport['sport_name'] ?>">
                Delete
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
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
                        <div id="sportNameError" class="invalid-feedback">
                            Please enter a unique sport name.
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

  <!-- Modal for Editing Sport -->
  <div class="modal fade" id="editSportModal" tabindex="-1" aria-labelledby="editSportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editSportModalLabel">Edit Sport</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editSportForm">
            <input type="hidden" id="edit_sport_id">
            <div class="mb-3">
              <label for="edit_sport_name" class="form-label">Sport Name</label>
              <input type="text" id="edit_sport_name" class="form-control" required>
              <div class="invalid-feedback">Please enter a sport name.</div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveSportChangesBtn">Save Changes</button>
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


</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Add Sport
    document.getElementById('addSportBtn').addEventListener('click', function () {
        const sportNameInput = document.getElementById('sport_name');
        const sportName = sportNameInput.value.trim();
        
        // Clear previous invalid feedback
        sportNameInput.classList.remove('is-invalid');

        // Check for empty input
        if (!sportName) {
            sportNameInput.classList.add('is-invalid');
            return;
        }

        // Send AJAX request to add sport
        fetch('../MAIN/roles/admin_/sports.php', { // Update this path if necessary
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'add_sport',
                sport_name: sportName
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Response from server:', data); // Log the response

            if (data.success) {
                // Add the new sport to the table dynamically
                const tableBody = document.querySelector('#sports_table tbody');
                tableBody.insertAdjacentHTML('beforeend', `
                    <tr id="sport-row-${data.sport_id}">
                        <td class="sport-name">${data.sport_name}</td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-sport-btn px-3" 
                                data-sport-id="${data.sport_id}" 
                                data-sport-name="${data.sport_name}">Edit</button>
                            <button class="btn btn-danger btn-sm delete-sport-btn px-3" 
                                data-sport-id="${data.sport_id}" 
                                data-sport-name="${data.sport_name}">Delete</button>
                        </td>
                    </tr>
                `);

                // Reset input field after adding the sport
                sportNameInput.value = '';
            } else {
                alert(data.message || 'Failed to add sport.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was a problem with the request.');
        });
    });

    // Remove invalid class when input is changed
    document.getElementById('sport_name').addEventListener('input', function () {
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
        }
    });
});

  document.querySelectorAll('.edit-sport-btn').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const sportId = this.dataset.sportId;
      fetch(`../MAIN/edit/edit_sport_admin.php?sport_id=${sportId}`)
        .then(response => response.text())
        .then(html => {
          const modalBody = document.querySelector('#editSportModal .modal-body');
          modalBody.innerHTML = html;
          // Show the modal
          new bootstrap.Modal(document.getElementById('editSportModal')).show();
        })
        .catch(err => console.error(err));
    });
  });

  document.getElementById("createSportBtn").addEventListener("click", () => {
    const form = document.getElementById("addSportForm");
    const formData = new FormData(form);

    // Reset errors
    const input = form.querySelector("input");
    input.classList.remove("is-invalid");
    const invalidFeedback = input.nextElementSibling;
    if (invalidFeedback) {
        invalidFeedback.style.display = "none";
    }

    let isValid = true;

    // Validate sport name
    if (!input.value.trim()) {
        input.classList.add("is-invalid");
        invalidFeedback.style.display = "block";
        isValid = false;
    }

    if (!isValid) return;

    const data = {
        action: "create_sport",
        sport_name: formData.get("sport_name"),
    };

    fetch("../MAIN/roles/admin_/sports.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data),
    })
        .then((response) => response.json())
        .then((result) => {
            if (result.success) {
                const sport = result.sport;
                const tbody = document.querySelector("#sports_table tbody");

                tbody.insertAdjacentHTML(
                    "beforeend",
                    `<tr id="sport-row-${sport.sport_id}">
                        <td>${sport.sport_name}</td>
                        <td class="text-center">
                            <button class="btn btn-warning btn-sm edit-sport-btn px-3" data-sport-id="${sport.sport_id}">Edit</button>
                            <button class="btn btn-danger btn-sm delete-sport-btn px-3" data-sport-id="${sport.sport_id}" data-sport-name="${sport.sport_name}">Delete</button>
                        </td>
                    </tr>`
                );

                form.reset();
                bootstrap.Modal.getInstance(document.getElementById("addSportModal")).hide();
            } else {
                if (result.message === "Sport name already exists.") {
                    const sportNameError = document.getElementById("sportNameError");
                    sportNameError.textContent = result.message;
                    input.classList.add("is-invalid");
                    sportNameError.style.display = "block";
                } else {
                    alert(result.message || "Failed to create sport.");
                }
            }
        })
        .catch((error) => console.error("Error:", error));
});

// Real-time validation
document.getElementById("sport_name").addEventListener("input", function() {
    const sportNameError = document.getElementById("sportNameError");
    sportNameError.style.display = "none";
    this.classList.remove("is-invalid");
});




  document.getElementById('saveSportChangesBtn').addEventListener('click', () => {
    const sportId = document.getElementById('edit_sport_id').value;
    const sportName = document.getElementById('edit_sport_name').value.trim();
    if (!sportName) {
      document.getElementById('edit_sport_name').classList.add('is-invalid');
      return;
    }

    fetch('sports.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'edit_sport',
          sport_id: sportId,
          sport_name: sportName
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const row = document.getElementById(`sport-row-${sportId}`);
          row.querySelector('.sport-name').textContent = sportName;
          bootstrap.Modal.getInstance(document.getElementById('editSportModal')).hide();
        } else {
          alert(data.message || 'Failed to update sport.');
        }
      });
  });



  

  function sortSportsTable() {
    const rows = Array.from(document.querySelectorAll('#sports_table tbody tr'));
    rows.sort((a, b) => a.querySelector('.sport-name').textContent.localeCompare(b.querySelector('.sport-name').textContent));
    rows.forEach(row => row.parentNode.appendChild(row));
  }

  function searchSport() {
    const input = document.getElementById('search_sport').value.toUpperCase();
    const rows = document.querySelectorAll('#sports_table tbody tr');
    rows.forEach(row => {
      const sportName = row.querySelector('.sport-name').textContent.toUpperCase();
      row.style.display = sportName.includes(input) ? '' : 'none';
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Save Changes Button Logic
    document.getElementById('saveSportChangesBtn').addEventListener('click', function() {
      // Get the form inside the modal
      const form = document.querySelector('#editSportModal .modal-body form');

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
      fetch('../MAIN/edit/edit_sport_admin.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            // Update the table dynamically
            const sportId = formData.get('sport_id');
            const sportRow = document.querySelector(`#sport-row-${sportId}`);
            if (sportRow) {
              sportRow.querySelector('.sport-name').textContent = formData.get('sport_name');
            }

            // Hide the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editSportModal'));
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
    document.querySelectorAll('#editSportModal .modal-body input[required]').forEach(input => {
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

  let sportToDeleteId = null; // Store the ID of the sport to delete
  let sportToDeleteName = null;

// Attach event listener to delete buttons
document.querySelectorAll('.delete-sport-btn').forEach(button => {
    button.addEventListener('click', function () {
        sportToDeleteId = this.dataset.sportId; // Get sport ID from the button
        const sportName = this.dataset.sportName; // Get sport name from the button

        // Update modal message with the sport name
        document.getElementById('confirmationMessage').textContent = `Are you sure you want to delete this sport?`;

        // Show the confirmation modal
        const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        modal.show();
    });
});

// Handle delete confirmation
document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
    if (!sportToDeleteId) return;

    // Send AJAX request to delete the sport
    fetch('../main/roles/admin_/sports.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'delete_sport',
            sport_id: sportToDeleteId,
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the sport row from the table
            const sportRow = document.getElementById(`sport-row-${sportToDeleteId}`);
            if (sportRow) {
                sportRow.remove();
            }

            // Reset the sport ID after deletion
            sportToDeleteId = null;

            // Hide the confirmation modal
            bootstrap.Modal.getInstance(document.getElementById('confirmationModal')).hide();
        } else {
            alert(data.message || 'Failed to delete the sport.');
        }
    })
    .catch(error => {
        console.error('Error during delete operation:', error);
        alert('An error occurred while processing the delete request.');
    });
});
  
</script>