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

// Fetch all sports with facilitators
$query = $conn->prepare("
       SELECT 
           s.sport_id,
           s.sport_name,
           s.event_id,
           s.sport_image,
           s.sport_description,
           s.sport_location,
           s.sport_time,
           s.sport_date,
           GROUP_CONCAT(DISTINCT CONCAT(u.first_name, ' ', u.last_name)) as facilitator_names,
           GROUP_CONCAT(DISTINCT u.user_id) as facilitator_ids
       FROM sports s
       LEFT JOIN sport_facilitators sf ON s.sport_id = sf.sport_id
       LEFT JOIN users u ON sf.user_id = u.user_id
       GROUP BY 
           s.sport_id,
           s.sport_name,
           s.event_id,
           s.sport_image,
           s.sport_description,
           s.sport_location,
           s.sport_time,
           s.sport_date
");
$query->execute();
$sports = $query->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sport_name'])) {
        $sportName = $_POST['sport_name'];
        $sportDescription = $_POST['sport_description'];
        $sportDate = $_POST['sport_date'];
        $sportTime = $_POST['sport_time'];
        $sportLocation = $_POST['sport_location'];

        // Handle image upload
        $imageData = null;
        if (!empty($_FILES['sport_image']['name'])) {
            $imageData = base64_encode(file_get_contents($_FILES['sport_image']['tmp_name']));
        }

        // Insert sport into database
        $query = $conn->prepare("INSERT INTO sports (sport_name, sport_description, sport_date, sport_time, sport_location, sport_image) VALUES (:sport_name, :sport_description, :sport_date, :sport_time, :sport_location, :sport_image)");
        $query->bindParam(':sport_name', $sportName);
        $query->bindParam(':sport_description', $sportDescription);
        $query->bindParam(':sport_date', $sportDate);
        $query->bindParam(':sport_time', $sportTime);
        $query->bindParam(':sport_location', $sportLocation);
        $query->bindParam(':sport_image', $imageData);

        if ($query->execute()) {
            $newSportId = $conn->lastInsertId();

            // Save facilitators to sport_facilitators table
            if (isset($_POST['facilitators'])) {
                $facilitators = json_decode($_POST['facilitators']);
                $insertFacilitator = $conn->prepare("INSERT INTO sport_facilitators (sport_id, user_id) VALUES (:sport_id, :user_id)");

                foreach ($facilitators as $facilitatorId) {
                    $insertFacilitator->execute([
                        ':sport_id' => $newSportId,
                        ':user_id' => $facilitatorId
                    ]);
                }
            }

            // Fetch complete sport data including facilitators
            $query = $conn->prepare("
            SELECT 
                s.*,
                GROUP_CONCAT(DISTINCT CONCAT(u.first_name, ' ', u.last_name) SEPARATOR '|') as facilitator_names
            FROM sports s
            LEFT JOIN sport_facilitators sf ON s.sport_id = sf.sport_id
            LEFT JOIN users u ON sf.user_id = u.user_id
            WHERE s.sport_id = :sport_id
            GROUP BY s.sport_id
        ");

            $query->execute([':sport_id' => $newSportId]);
            $newSport = $query->fetch(PDO::FETCH_ASSOC);

            echo json_encode(['status' => 'success', 'sport' => $newSport]);
            exit();
        }
    }
}
if (!$sports) {
    $sports = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <style>
        .sport-card {
            position: relative;
            background-size: cover;
            background-position: center;
            height: 250px;
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            cursor: pointer;
        }

        .sport-card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.7);
        }

        .sport-name-overlay {
            position: absolute;
            bottom: 0;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            text-align: center;
            padding: 15px;
            border-radius: 0 0 10px 10px;
        }

        .sport-description {
            font-size: 0.9em;
            margin-top: 5px;
            text-align: center;
            color: white;
            max-height: 40px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container my-5">
        <h1 class="text-center text-maroon">Sports</h1>
        <br>

        <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#addSportModal">Add Sport</button>

        <div class="row">
            <?php foreach ($sports as $sport): ?>
                <div class="col-md-4 mb-4">
                    <div class="sport-card shadow-lg"
                        style="background-image: url('data:image/jpeg;base64,<?= $sport['sport_image'] ?>');"
                        onclick="showSportDetails(<?= $sport['sport_id'] ?>)">
                        <div class="sport-name-overlay">
                            <h5><?= htmlspecialchars($sport['sport_name']) ?></h5>
                            <p class="sport-description"><?= htmlspecialchars($sport['sport_description']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Sport Details Modal -->
    <div class="modal fade" id="sportDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sport-name"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <img id="sport-image" alt="Sport Image" class="mb-4 w-100">
                    <p><strong>Description:</strong> <span id="sport-description"></span></p>
                    <p><strong>Time:</strong> <span id="sport-time"></span></p>
                    <p><strong>Location:</strong> <span id="sport-location"></span></p>
                    <p><strong>Date:</strong> <span id="sport-date"></span></p>
                    <p><strong>Facilitators:</strong></p>
                    <div id="sport-facilitators" class="mb-3"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-warning btn-sm edit-sport-btn px-3" id="editSportBtn">Edit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Sport Modal -->
    <div class="modal fade" id="addSportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addSportForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Sport</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="sport_name" class="form-label">Sport Name</label>
                            <input type="text" class="form-control" id="sport_name" name="sport_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="sport_description" class="form-label">Description</label>
                            <textarea class="form-control" id="sport_description" name="sport_description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="sport_date" class="form-label">Sport Date</label>
                            <input type="date" class="form-control" id="sport_date" name="sport_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="sport_time" class="form-label">Sport Time</label>
                            <input type="time" class="form-control" id="sport_time" name="sport_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="sport_location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="sport_location" name="sport_location" required>
                        </div>
                        <div class="mb-3">
                            <label for="sport_image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="sport_image" name="sport_image" required>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-secondary" id="select_facilitators_btn">Select Facilitators</button>
                            <div id="selected_facilitators_list" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Sport</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Facilitator Selection Modal -->
    <div class="modal fade" id="facilitatorModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <!-- Content will be loaded dynamically -->
            <!-- Content will be loaded dynamically -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let sportDetails = <?php echo json_encode($sports); ?>;

        function showSportDetails(sportId) {
            const sport = sportDetails.find(s => s.sport_id == sportId);
            if (sport) {
                document.getElementById('sport-name').innerText = sport.sport_name;
                document.getElementById('sport-time').innerText = sport.sport_time;
                document.getElementById('sport-location').innerText = sport.sport_location;
                document.getElementById('sport-date').innerText = sport.sport_date;
                document.getElementById('sport-description').innerText = sport.sport_description;
                document.getElementById('sport-image').src = 'data:image/jpeg;base64,' + sport.sport_image;

                // Display facilitators
                const facilitatorNames = sport.facilitator_names ? sport.facilitator_names.split('|') : [];
                const facilitatorsHtml = facilitatorNames.length > 0 ?
                    facilitatorNames.map(name => `
                <span class="badge bg-primary me-2 mb-1">
                    <i class="bi bi-person-fill me-1"></i>${name.trim()}
                </span>`).join('') :
                    '<span class="text-muted">No facilitators assigned</span>';

                document.getElementById('sport-facilitators').innerHTML = facilitatorsHtml;

                new bootstrap.Modal(document.getElementById('sportDetailsModal')).show();
            }
        }



        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('select_facilitators_btn').addEventListener('click', function() {
                const facilitatorModal = new bootstrap.Modal(document.getElementById('facilitatorModal'));

                fetch('../main/roles/admin_/select_facilitators.php')
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector('#facilitatorModal .modal-dialog').innerHTML = html;
                        facilitatorModal.show();

                        // Add event listener after modal content is loaded
                        document.getElementById('confirm_facilitators').addEventListener('click', function() {
                            const selectedFacilitators = [];
                            document.querySelectorAll('.facilitator-checkbox:checked').forEach(checkbox => {
                                selectedFacilitators.push({
                                    id: checkbox.value,
                                    name: checkbox.closest('tr').querySelector('td:nth-child(3)').textContent
                                });
                            });

                            const facilitatorsList = document.getElementById('selected_facilitators_list');
                            facilitatorsList.innerHTML = selectedFacilitators.map(f =>
                                `<span class="badge bg-primary me-1">${f.name}</span>`
                            ).join('');

                            facilitatorModal.hide();
                        });
                    });
            });
        });


        document.addEventListener('DOMContentLoaded', () => {
            // Close facilitator modal before opening sport modal
            document.getElementById('addSportForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                // Ensure any open modals are properly closed
                const facilitatorModal = bootstrap.Modal.getInstance(document.getElementById('facilitatorModal'));
                if (facilitatorModal) {
                    facilitatorModal.hide();
                }

                fetch('../main/roles/admin_/sports.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('addSportModal'));
                            modal.hide();
                            window.location.reload();
                        }
                    });
            });
        });
    </script>
</body>

</html>