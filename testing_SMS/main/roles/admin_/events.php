<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit();
}

require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

// Handle AJAX delete request for events
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['ajax']) && $data['ajax'] === 'delete_event') {
  $event_id = $data['event_id'] ?? null;

  if (!$event_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid request or missing event_id.']);
    exit();
  }

  $query = $conn->prepare("DELETE FROM events WHERE event_id = :event_id");
  $query->bindParam(':event_id', $event_id, PDO::PARAM_INT);

  if ($query->execute()) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'error' => 'Unable to delete event.']);
  }
  exit();
}


// Handle AJAX add request for new event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['ajax']) && $data['ajax'] === 'add_event') {
  $event_name = $data['event_name'] ?? null;
  $event_date = $data['event_date'] ?? null;

  if (!$event_name || !$event_date) {
    echo json_encode(['success' => false, 'error' => 'Invalid request or missing event data.']);
    exit();
  }

  $query = $conn->prepare("INSERT INTO events (event_name, event_date) VALUES (:event_name, :event_date)");
  $query->bindParam(':event_name', $event_name, PDO::PARAM_STR);
  $query->bindParam(':event_date', $event_date, PDO::PARAM_STR);

  if ($query->execute()) {
    $event_id = $conn->lastInsertId(); // Get the ID of the newly inserted event
    echo json_encode(['success' => true, 'event_id' => $event_id, 'event_name' => $event_name, 'event_date' => $event_date]);
  } else {
    echo json_encode(['success' => false, 'error' => 'Unable to add event.']);
  }
  exit();
}


// Fetch all events
$query = $conn->prepare("SELECT * FROM events");
$query->execute();
$events = $query->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../SMS/css/style.css">
</head>

<body>
  <div class="container mt-5">
    <h2>Event Management</h2>
    <div class="mb-3">
      <input type="text" id="search_event_bar" class="form-control" placeholder="Search for events..." onkeyup="searchEvent()">
    </div>

    <!-- Events Table -->
    <table class="table table-hover align-middle table-bordered rounded-3 overflow-hidden shadow" id="events_table">
      <thead class="table-primary">
        <tr class="text-center">
          <th>Event</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($events as $event): ?>
          <tr id="event-row-<?= $event['event_id'] ?>">
            <td class="event_name"><?= htmlspecialchars($event['event_name']) ?></td>
            <td class="event_date"><?= htmlspecialchars($event['event_date']) ?></td>
            <td class="text-center">
              <button class="btn btn-warning btn-sm edit-event-btn" data-event-id="<?= $event['event_id'] ?>">Edit</button>
              <button class="btn btn-danger btn-sm delete-event-btn" data-event-id="<?= $event['event_id'] ?>" data-event-name="<?= htmlspecialchars($event['event_name']) ?>">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h3>Add New Event</h3>
    <form action="index.php" method="post">
      <div class="mb-3">
        <label for="event_name" class="form-label">Event Name</label>
        <input type="text" id="event_name" name="event_name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="event_date" class="form-label">Event Date</label>
        <input type="date" id="event_date" name="event_date" class="form-control" required>
      </div>
      <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
    </form>
  </div>

  <!-- Modal for Editing Event -->
  <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="editEventModalBody">
          <!-- Content will be loaded dynamically via AJAX -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveChangesBtn">Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for Deleting Event -->
  <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteEventModalLabel">Delete Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="deleteEventModalBody">
          <!-- Content will be dynamically set -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteEventBtn">Delete</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function searchEvent() {
      const input = document.getElementById("search_event_bar").value.toUpperCase();
      const table = document.getElementById("events_table");
      const rows = table.getElementsByTagName("tr");

      for (let i = 1; i < rows.length; i++) {
        const eventNameCell = rows[i].getElementsByTagName("td")[0];
        const eventName = eventNameCell ? eventNameCell.textContent || eventNameCell.innerText : "";
        rows[i].style.display = eventName.toUpperCase().includes(input) ? "" : "none";
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      let eventToDeleteId = null;
      let eventToDeleteName = null;

      // Attach delete button click listeners
      document.querySelectorAll('.delete-event-btn').forEach(button => {
        button.addEventListener('click', function() {
          eventToDeleteId = this.getAttribute('data-event-id');
          eventToDeleteName = this.getAttribute('data-event-name');
          document.getElementById('deleteEventModalBody').textContent = `Are you sure you want to delete the event "${eventToDeleteName}"?`;

          const modal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
          modal.show();
        });
      });

      // Confirm delete
      document.getElementById('confirmDeleteEventBtn').addEventListener('click', () => {
        if (eventToDeleteId) {
          fetch('../main/roles/admin_/events.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                ajax: 'delete_event',
                event_id: eventToDeleteId
              }),
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                document.getElementById(`event-row-${eventToDeleteId}`).remove();
                // alert('Event deleted successfully.');
              } else {
                alert(data.error || 'Failed to delete event.');
              }
            })
            .catch(error => {
              console.error('Error during delete operation:', error);
              // alert('An error occurred while deleting the event.');
            })
            .finally(() => {
              eventToDeleteId = null;
              eventToDeleteName = null;
              bootstrap.Modal.getInstance(document.getElementById('deleteEventModal')).hide();
            });
        }
      });
    });




    // Edit Event AJAX
    function editEvent(eventId) {
      fetch(`../main/edit/edit_event_admin.php?event_id=${eventId}`)
        .then(response => response.text())
        .then(html => {
          document.getElementById('editEventModalBody').innerHTML = html;
          const modal = new bootstrap.Modal(document.getElementById('editEventModal'));
          modal.show();

          // Attach form submit listener after modal content loads
          document.getElementById('saveChangesBtn').addEventListener('click', function() {
            const form = document.querySelector('#editEventModalBody form');
            const formData = new FormData(form);

            fetch('../main/edit/edit_event_admin.php', {
                method: 'POST',
                body: formData
              })
              .then(response => response.json())
              .then(result => {
                if (result.success) {
                  const row = document.getElementById(`event-row-${eventId}`);
                  row.querySelector('.event_name').textContent = formData.get('event_name');
                  row.querySelector('.event_date').textContent = formData.get('event_date');
                  modal.hide();
                } else {
                  alert(result.message || 'Failed to save changes.');
                }
              })
              .catch(error => console.error('Error:', error));
          });
        })
        .catch(err => console.error(err));
    }

    // Attach event listeners
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.edit-event-btn').forEach(button => {
        button.addEventListener('click', function() {
          const eventId = this.dataset.eventId;
          editEvent(eventId);
        });
      });
    });

    document.addEventListener('DOMContentLoaded', () => {
      // Handle the form submission for adding a new event
      document.querySelector('form[action="index.php"]').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        const eventName = document.getElementById('event_name').value;
        const eventDate = document.getElementById('event_date').value;

        fetch('../main/roles/admin_/events.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              ajax: 'add_event',
              event_name: eventName,
              event_date: eventDate
            }),
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Add the new event row to the table
              const newRow = document.createElement('tr');
              newRow.id = `event-row-${data.event_id}`;
              newRow.innerHTML = `
                    <td class="event_name">${data.event_name}</td>
                    <td class="event_date">${data.event_date}</td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm edit-event-btn" data-event-id="${data.event_id}">Edit</button>
                        <button class="btn btn-danger btn-sm delete-event-btn" data-event-id="${data.event_id}" data-event-name="${data.event_name}">Delete</button>
                    </td>
                `;
              document.querySelector('#events_table tbody').appendChild(newRow);

              // Optionally reset the form fields after submission
              document.getElementById('event_name').value = '';
              document.getElementById('event_date').value = '';

              alert('Event added successfully!');
            } else {
              alert(data.error || 'Failed to add event.');
            }
          })
          .catch(error => {
            console.error('Error during add operation:', error);
            alert('An error occurred while adding the event.');
          });
      });
    });


    document.addEventListener('DOMContentLoaded', () => {
      let eventToDeleteId = null;
      let eventToDeleteName = null;

      // Handle Delete Button Click
      document.querySelectorAll('.delete-event-btn').forEach(button => {
        button.addEventListener('click', function() {
          eventToDeleteId = this.getAttribute('data-event-id');
          eventToDeleteName = this.getAttribute('data-event-name');
          document.getElementById('deleteEventModalBody').textContent = `Are you sure you want to delete the event "${eventToDeleteName}"?`;

          const modal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
          modal.show();
        });
      });

      // Confirm Deletion
      document.getElementById('confirmDeleteEventBtn').addEventListener('click', () => {
        if (eventToDeleteId) {
          fetch('../main/roles/admin_/events.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                ajax: 'delete_event',
                event_id: eventToDeleteId
              }),
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                document.getElementById(`event-row-${eventToDeleteId}`).remove();
              } else {
                alert(data.error || 'Failed to delete event.');
              }
            })
            .catch(error => {
              console.error('Error during delete operation:', error);
              // alert('An error occurred while deleting the eventz.');
            })
            .finally(() => {
              eventToDeleteId = null;
              eventToDeleteName = null;
              bootstrap.Modal.getInstance(document.getElementById('deleteEventModal')).hide();
            });
        }
      });
    });
  </script>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <?php include_once('C:/xampp/htdocs/testing_SMS/main/includes/_head.php'); ?>
</body>

</html>