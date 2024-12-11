<?php
require_once '../MAIN/database/database.class.php';
require_once '../MAIN/includes/clean_function.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = (new Database())->connect();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die(json_encode(["success" => false, "message" => "Access denied."]));
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    $registrationId = filter_input(INPUT_POST, 'registration_id', FILTER_SANITIZE_NUMBER_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

    if (!$registrationId || !in_array($action, ['approve', 'decline'], true)) {
        echo json_encode(["success" => false, "message" => "Invalid input provided."]);
        exit;
    }

    $newStatus = $action === 'approve' ? 'approved' : 'declined';

    try {
        $updateQuery = $conn->prepare("
            UPDATE registrations 
            SET status = :status 
            WHERE registration_id = :registration_id
        ");
        $updateQuery->bindParam(':status', $newStatus, PDO::PARAM_STR);
        $updateQuery->bindParam(':registration_id', $registrationId, PDO::PARAM_INT);

        if ($updateQuery->execute()) {
            echo json_encode(["success" => true, "message" => "Registration successfully {$newStatus}."]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update the database."]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Database error occurred."]);
    }
    exit;
}

// Existing logic for fetching pending registrations
$query = $conn->prepare("
SELECT 
    r.registration_id, 
    r.last_name, 
    r.first_name, 
    r.contact_info, 
    r.age,
    r.height,
    r.weight,
    r.bmi,
    r.medcert,
    r.cor_pic,
    r.id_pic,
    r.sex, 
    r.course, 
    r.section, 
    s.sport_name 
FROM registrations r
JOIN sports s ON r.sport_id = s.sport_id
WHERE r.status = 'pending'
");
$query->execute();
$pendingRegistrations = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderator Dashboard</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">Pending Registrations</h2>
    <div id="feedback"></div>

    <?php if (empty($pendingRegistrations)): ?>
        <div class="alert alert-info">There are no pending registrations at the moment.</div>
    <?php else: ?>
        <table class="table table-striped" id="registrations-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Age</th>
                    <th>Height</th>
                    <th>Weight</th>
                    <th>BMI</th>
                    <th>Medical Certificate</th>
                    <th>Cor Pic</th>
                    <th>ID Pic</th>
                    <th>Sex</th>
                    <th>Course</th>
                    <th>Section</th>
                    <th>Sport</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingRegistrations as $registration): ?>
                    <tr id="registration-<?= $registration['registration_id'] ?>">
                        <td><?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) ?></td>
                        <td><?= htmlspecialchars($registration['contact_info']) ?></td>
                        <td><?= htmlspecialchars($registration['age']) ?></td>
                        <td><?= htmlspecialchars($registration['height']) ?></td>
                        <td><?= htmlspecialchars($registration['weight']) ?></td>
                        <td><?= htmlspecialchars($registration['bmi']) ?></td>
                        <td><a href="#" class="view-document" data-src="<?= htmlspecialchars($registration['medcert']) ?>">View</a></td>
                        <td><a href="#" class="view-document" data-src="<?= htmlspecialchars($registration['cor_pic']) ?>">View</a></td>
                        <td><a href="#" class="view-document" data-src="<?= htmlspecialchars($registration['id_pic']) ?>">View</a></td>
                        <td><?= htmlspecialchars($registration['sex']) ?></td>
                        <td><?= htmlspecialchars($registration['course']) ?></td>
                        <td><?= htmlspecialchars($registration['section']) ?></td>
                        <td><?= htmlspecialchars($registration['sport_name']) ?></td>
                        <td >
                            <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-success btn-sm action-btn " data-id="<?= $registration['registration_id'] ?>" data-action="approve">Approve</button>
                            <button class="btn btn-danger btn-sm action-btn" data-id="<?= $registration['registration_id'] ?>" data-action="decline">Decline</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Modal for viewing document -->
    <div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDocumentModalLabel">View Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="documentImage" src="" alt="Document" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.view-document').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const src = this.dataset.src;

            // Update the modal image source
            const documentImage = document.getElementById('documentImage');
            documentImage.src = src;

            // Show the modal
            const viewDocumentModal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
            viewDocumentModal.show();
        });
    });
});

$(document).ready(function() {
    $('.action-btn').click(function(e) {
        e.preventDefault();
        
        const button = $(this);
        const registrationId = button.data('id');
        const action = button.data('action');
        
        $.ajax({
            url: '',
            method: 'POST',
            data: {
                ajax: 'true',
                registration_id: registrationId,
                action: action
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#feedback').html(`<div class="alert alert-success">${response.message}</div>`);
                    $(`#registration-${registrationId}`).remove();
                } else {
                    $('#feedback').html(`<div class="alert alert-danger">${response.message}</div>`);
                }
            },
        });
    });
});
</script>
</body>
</html>
