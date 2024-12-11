<?php
ini_set('display_errors', 0); // Do not display errors to the user
ini_set('log_errors', 1); // Log errors to server logs
error_reporting(E_ALL); // Report all errors for logging

require_once '../MAIN/database/database.class.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$conn = (new Database())->connect();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in first.");
}

$student_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sport_id'])) {
    $sport_id = $_POST['sport_id'];

    if ($sport_id) {
        // Register the student for the selected sport
        $query = $conn->prepare("INSERT INTO registrations (student_id, sport_id) VALUES (:student_id, :sport_id)");
        $query->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $query->bindParam(':sport_id', $sport_id, PDO::PARAM_INT);

        try {
            if ($query->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Registration successful.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sport ID is required.']);
        exit();
    }
}

// Fetch available sports
$sportsQuery = $conn->prepare("SELECT * FROM sports");
$sportsQuery->execute();
$sports = $sportsQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div id="sports_section" class="dashboard-section">
    <h2 class="my-4">Available Sports</h2>

    <div class="mb-4">
        <input type="text" id="searchSports" class="form-control" placeholder="Search for sports" onkeyup="filterSports()">
    </div>

    <div class="row" id="sportsContainer">
        <?php foreach ($sports as $sport): ?>
            <div class="col-md-4 mb-4 sport-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($sport['sport_name']) ?></h5>
                        <button class="btn btn-primary" onclick="registerSport(<?= $sport['sport_id'] ?>)">Register</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Sport Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registrationModalLabel">Register for Sport</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to register for this sport?</p>
                <input type="hidden" id="sport_id" name="sport_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submitRegistrationBtn">Register</button>
            </div>
        </div>
    </div>
</div>

<script>
function filterSports() {
    var input = document.getElementById("searchSports").value.toLowerCase();
    var cards = document.getElementsByClassName("sport-card");
    
    for (var i = 0; i < cards.length; i++) {
        var sportName = cards[i].getElementsByClassName("card-title")[0].textContent.toLowerCase();
        if (sportName.includes(input)) {
            cards[i].style.display = "";
        } else {
            cards[i].style.display = "none";
        }
    }
}

function registerSport(sport_id) {
    document.getElementById('sport_id').value = sport_id;
    var modal = new bootstrap.Modal(document.getElementById('registrationModal'));
    modal.show();
}

document.getElementById('submitRegistrationBtn').addEventListener('click', function() {
    submitRegistration();
});

function submitRegistration() {
    var sport_id = document.getElementById('sport_id').value;
    $.ajax({
        type: 'POST',
        url: window.location.href,
        data: { sport_id: sport_id },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('AJAX error: ' + textStatus + ', ' + errorThrown);
            console.error(jqXHR.responseText);
        }
    });
}
</script>
</body>
</html>
