<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

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
    <title>Events for Students</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Upcoming Events</h2>

        <!-- Search Bar -->
        <div class="mb-3">
            <input type="text" id="search_event_bar" class="form-control" 
                   placeholder="Search for events..." 
                   onkeyup="searchEvent()" 
                   aria-label="Search for events">
        </div>

        <!-- Events Table -->
        <table class="table table-hover align-middle table-bordered rounded-3 overflow-hidden shadow" id="events_table">
            <thead class="table-primary">
                <tr class="text-center">
                    <th>Event</th>
                    <th>Date</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="3" class="text-center">No events available.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <tr id="event-row-<?= $event['event_id'] ?>">
                            <td class="event_name"><?= htmlspecialchars($event['event_name']) ?></td>
                            <td class="event_date"><?= htmlspecialchars($event['event_date']) ?></td>
                            <td class="event_description"><?= htmlspecialchars($event['event_description'] ?? 'No description available') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- No Results Message -->
        <div id="no_results" class="text-center mt-3" style="display: none;">
            <p class="text-muted">No events found.</p>
        </div>
    </div>

    <script>
        function searchEvent() {
            const input = document.getElementById("search_event_bar").value.toUpperCase();
            const table = document.getElementById("events_table");
            const rows = table.getElementsByTagName("tr");
            let hasVisibleRow = false;

            for (let i = 1; i < rows.length; i++) {
                const eventNameCell = rows[i].getElementsByTagName("td")[0];
                const eventName = eventNameCell ? eventNameCell.textContent || eventNameCell.innerText : "";
                const isMatch = eventName.toUpperCase().includes(input);
                rows[i].style.display = isMatch ? "" : "none";
                if (isMatch) hasVisibleRow = true;
            }

            // Show or hide the "No results" message
            document.getElementById("no_results").style.display = hasVisibleRow ? "none" : "block";
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
