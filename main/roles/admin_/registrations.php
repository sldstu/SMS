<?php
require_once '../MAIN/database/database.class.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admin only.");
}

// Database connection
$conn = (new Database())->connect();

// Fetch all student registrations
$query = $conn->prepare("
    SELECT 
        r.registration_id,
        u.first_name,
        u.last_name,
        e.event_name,
        s.sport_name
    FROM registrations r
    JOIN users u ON r.student_id = u.user_id
    JOIN sports s ON r.sport_id = s.sport_id
    JOIN events e ON s.event_id = e.event_id
");
$query->execute();
$registrations = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrations Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center">Registrations Management</h1>
        <br>

        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>Name</th>
                        <th>Event Name</th>
                        <th>Sport Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $registration): ?>
                        <tr>
                            <td><?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) ?></td>
                            <td><?= htmlspecialchars($registration['event_name']) ?></td>
                            <td><?= htmlspecialchars($registration['sport_name']) ?></td>
                            <td class="text-center">
                                <button class="btn btn-info btn-sm" onclick="viewDetails(<?= $registration['registration_id'] ?>)">View Details</button>
                                <button class="btn btn-warning btn-sm" onclick="editRegistration(<?= $registration['registration_id'] ?>)">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteRegistration(<?= $registration['registration_id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Name:</strong> <span id="student-name"></span></p>
                    <p><strong>Contact Info:</strong> <span id="contact-info"></span></p>
                    <p><strong>Birthday:</strong> <span id="student-birthday"></span></p>
                    <p><strong>Address:</strong> <span id="student-address"></span></p>
                    <p><strong>Sex:</strong> <span id="student-sex"></span></p>
                    <p><strong>Course:</strong> <span id="student-course"></span></p>
                    <p><strong>Section:</strong> <span id="student-section"></span></p>
                    <p><strong>COR:</strong> <img id="student-cor" alt="COR Image" class="img-fluid"></p>
                    <p><strong>ID Image:</strong> <img id="student-id_image" alt="ID Image" class="img-fluid"></p>
                    <p><strong>Medical Certificate:</strong> <img id="student-medcert" alt="Medical Certificate" class="img-fluid"></p>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" onclick="approveRegistration()">Approve</button>
                        <button type="button" class="btn btn-danger" onclick="declineRegistration()">Decline</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewDetails(registrationId) {
            $.ajax({
                url: '../main/roles/admin_/get_student_details.php',
                // or alternatively try:
                // url: '/sms/main/roles/admin_/get_student_details.php',
                method: 'GET',
                data: { registration_id: registrationId },
                dataType: 'json',
                success: function(data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
            
                    $('#student-name').text(data.first_name + ' ' + data.last_name);
                    $('#contact-info').text(data.contact_info);
                    $('#student-birthday').text(data.birthday);
                    $('#student-address').text(data.address);
                    $('#student-sex').text(data.sex);
                    $('#student-course').text(data.course);
                    $('#student-section').text(data.section);
                    $('#student-cor').attr('src', 'data:image/jpeg;base64,' + data.cor);
                    $('#student-id_image').attr('src', 'data:image/jpeg;base64,' + data.id_image);
                    $('#student-medcert').attr('src', 'data:image/jpeg;base64,' + data.medcert);

                    var modal = new bootstrap.Modal(document.getElementById('detailsModal'));
                    modal.show();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('AJAX error: ' + textStatus + ', ' + errorThrown);
                    console.error(jqXHR.responseText);
                }
            });
        }        function approveRegistration() {
            // Handle approve logic
        }

        function declineRegistration() {
            // Handle decline logic
        }

        function editRegistration(registrationId) {
            // Handle edit logic
        }

        function deleteRegistration(registrationId) {
            // Handle delete logic
        }
    </script>
</body>
</html>
