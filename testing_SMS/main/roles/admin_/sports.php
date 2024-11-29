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
  // Get the incoming JSON data
  $input = json_decode(file_get_contents('php://input'), true);

  // Validate the data
  if (!isset($input['sport_name']) || empty($input['sport_name'])) {
    echo json_encode(['success' => false, 'message' => 'Sport name is required.']);
    exit();
  }

  $sport_name = trim($input['sport_name']); // Sanitize input

  try {
    // Insert new sport into the database
    $query = $conn->prepare("INSERT INTO sports (sport_name) VALUES (:sport_name)");
    $query->bindParam(':sport_name', $sport_name);
    $query->execute();

    // Get the ID of the newly inserted sport
    $sport_id = $conn->lastInsertId();

    // Return success with sport data
    echo json_encode([
      'success' => true,
      'sport_id' => $sport_id,
      'sport_name' => $sport_name
    ]);
    exit();
  } catch (Exception $e) {
    // Log error in case of failure
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit();
  }


  if ($input['action'] === 'edit_sport') {
    $sport_id = $input['sport_id'];
    $sport_name = trim($input['sport_name']);
    if (empty($sport_name)) {
      echo json_encode(['success' => false, 'message' => 'Sport name is required.']);
      exit();
    }

    $query = $conn->prepare("UPDATE sports SET sport_name = :sport_name WHERE sport_id = :sport_id");
    $query->bindParam(':sport_name', $sport_name);
    $query->bindParam(':sport_id', $sport_id);
    $query->execute();
    echo json_encode(['success' => true]);
    exit();
  }

  if ($input['action'] === 'delete_sport') {
    $sport_id = $input['sport_id'];
    $query = $conn->prepare("DELETE FROM sports WHERE sport_id = :sport_id");
    $query->bindParam(':sport_id', $sport_id);
    $query->execute();
    echo json_encode(['success' => true]);
    exit();
  }

  echo json_encode(['success' => false, 'message' => 'Invalid action.']);
  exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get the incoming JSON data
  $input = json_decode(file_get_contents('php://input'), true);

  if (!isset($input['action'])) {
      echo json_encode(['success' => false, 'message' => 'No action specified.']);
      exit();
  }

  $action = $input['action'];

  if ($action === 'add_sport') {
      if (!isset($input['sport_name']) || empty(trim($input['sport_name']))) {
          echo json_encode(['success' => false, 'message' => 'Sport name is required.']);
          exit();
      }

      $sport_name = trim($input['sport_name']);

      try {
          // Insert new sport into the database
          $query = $conn->prepare("INSERT INTO sports (sport_name) VALUES (:sport_name)");
          $query->bindParam(':sport_name', $sport_name);
          $query->execute();

          // Get the ID of the newly inserted sport
          $sport_id = $conn->lastInsertId();

          // Return success with sport data
          echo json_encode([
              'success' => true,
              'sport_id' => $sport_id,
              'sport_name' => $sport_name,
          ]);
      } catch (Exception $e) {
          echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
      }
      exit();
  }

  echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
  exit();
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
            <td>
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

  <!-- Add New Sport -->
  <div class="mt-4">
    <h3>Add New Sport</h3>
    <div class="mb-3">
      <label for="sport_name" class="form-label">Sport Name</label>
      <input type="text" id="sport_name" class="form-control" required>
      <div class="invalid-feedback">Please enter a sport name.</div>
    </div>
    <button id="addSportBtn" class="btn btn-success">Add Sport</button>
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

  
</script>