<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

// Fetch all registrations
$query = $conn->prepare("SELECT * FROM registrations");
$query->execute();
$registrations = $query->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action'])) {
        try {
            if ($input['action'] === 'delete_registration' && isset($input['registration_id'])) {
                // Delete Registration
                $registrationId = $input['registration_id'];
                // Corrected Delete Query
                $query = $conn->prepare("DELETE FROM registrations WHERE registration_id = :id");
                $query->bindParam(':id', $registrationId, PDO::PARAM_INT);

                if ($query->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete registration.']);
                }
                exit();
            } elseif ($input['action'] === 'edit_registration' && isset($input['registration_data'])) {
                // Edit Registration
                $data = $input['registration_data'];
                $query = $conn->prepare(
                    "UPDATE registrations SET 
                        first_name = :first_name, 
                        last_name = :last_name,
                        contact_info = :contact_info,
                        age = :age,
                        sex = :sex,
                        course = :course,
                        section = :section,
                        height = :height,
                        weight = :weight,
                        bmi = :bmi
                    WHERE registration_id = :id"
                );
                $query->execute([
                    ':first_name' => $data['first_name'],
                    ':last_name' => $data['last_name'],
                    ':contact_info' => $data['contact_info'],
                    ':age' => $data['age'],
                    ':sex' => $data['sex'],
                    ':course' => $data['course'],
                    ':section' => $data['section'],
                    ':height' => $data['height'],
                    ':weight' => $data['weight'],
                    ':bmi' => $data['bmi'],
                    ':id' => $data['registration_id'],
                ]);
                echo json_encode(['success' => true]);
                exit();
            }
        } catch (Exception $e) {
            error_log('Error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error.']);
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Sport Registration</title>
    <!-- Add Bootstrap CSS -->
    <link rel="stylesheet" href="../../../sms/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div id="users_section" class="container mt-4">
        <h2 class="mb-4">Student Sport Registration</h2>
        <div class="table-responsive">
            <table id="users_table" class="table table-hover align-middle table-bordered rounded-3 overflow-hidden shadow">
                <thead class="table-primary">
                    <tr class="text-center">
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Contact Information</th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>Course</th>
                        <th>Section</th>
                        <th>Height</th>
                        <th>Weight</th>
                        <th>BMI</th>
                        <th>Medical Certificate</th>
                        <th>COR</th>
                        <th>ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $registration): ?>
                        <tr id="user-row-<?= $registration['student_id'] ?>">
                            <td><?= htmlspecialchars($registration['first_name']) ?></td>
                            <td><?= htmlspecialchars($registration['last_name']) ?></td>
                            <td><?= htmlspecialchars($registration['contact_info']) ?></td>
                            <td><?= htmlspecialchars($registration['age']) ?></td>
                            <td><?= htmlspecialchars($registration['sex']) ?></td>
                            <td><?= htmlspecialchars($registration['course']) ?></td>
                            <td><?= htmlspecialchars($registration['section']) ?></td>
                            <td><?= htmlspecialchars($registration['height']) ?></td>
                            <td><?= htmlspecialchars($registration['weight']) ?></td>
                            <td><?= htmlspecialchars($registration['bmi']) ?></td>
                            <td><a href="#" class="view-document" data-src="<?= htmlspecialchars($registration['medcert']) ?>">View</a></td>
                            <td><a href="#" class="view-document" data-src="<?= htmlspecialchars($registration['cor_pic']) ?>">View</a></td>
                            <td><a href="#" class="view-document" data-src="<?= htmlspecialchars($registration['id_pic']) ?>">View</a></td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-warning btn-sm edit-btn" data-id="<?= $registration['student_id'] ?>">Edit</button>
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="<?= $registration['student_id'] ?>">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

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
    </div>

    <!-- Add Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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

        $(document).ready(function () {
            // Delete functionality
            $('.delete-btn').click(function () {
                const userId = $(this).data('id');
                const row = $(`#user-row-${userId}`);
                if (confirm('Are you sure you want to delete this registration?')) {
                    // Simulate delete operation
                    row.remove();
                    alert(`Registration for student ID ${student_Id} deleted.`);
                }
            });
            $('.delete-btn').click(function () {
    const userId = $(this).data('id');
    const row = $(`#user-row-${userId}`);
    if (confirm('Are you sure you want to delete this registration?')) {
        $.ajax({
            url: '', // URL of the current PHP file
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'delete_registration',
                registration_id: userId
            }),
            success: function (response) {
                const data = JSON.parse(response);
                if (data.success) {
                    row.remove(); // Remove row from the table
                    alert(`Registration for student ID ${userId} deleted.`);
                } else {
                    alert('Failed to delete registration.');
                }
            }
        });
    }
});


            // Edit functionality
            $('.edit-btn').click(function () {
                const userId = $(this).data('id');
                const row = $(`#user-row-${userId}`);
                const fields = row.find('td');

                // Pre-fill edit form
                const formData = {
                    student_id: userId,
                    first_name: fields.eq(0).text().trim(),
                    last_name: fields.eq(1).text().trim(),
                    contact_info: fields.eq(2).text().trim(),
                    age: fields.eq(3).text().trim(),
                    sex: fields.eq(4).text().trim(),
                    course: fields.eq(5).text().trim(),
                    section: fields.eq(6).text().trim(),
                    height: fields.eq(7).text().trim(),
                    weight: fields.eq(8).text().trim(),
                    bmi: fields.eq(9).text().trim()
                };

                const modalContent = `
                    <form id="edit-form">
                        ${Object.entries(formData).map(([key, value]) => `
                            <div class="mb-3">
                                <label for="${key}" class="form-label">${key.replace('_', ' ').toUpperCase()}</label>
                                <input type="${key === 'age' || key === 'height' || key === 'weight' || key === 'bmi' ? 'number' : 'text'}" 
                                    id="${key}" name="${key}" class="form-control" value="${value}" required>
                            </div>
                        `).join('')}
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                `;

                $('#edit-form').submit(function (e) {
    e.preventDefault();
    const updatedData = Object.fromEntries(new FormData(this).entries());

    $.ajax({
        url: '', // URL of the current PHP file
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'edit_registration',
            registration_data: updatedData
        }),
        success: function (response) {
            const data = JSON.parse(response);
            if (data.success) {
                const row = $(`#user-row-${updatedData.student_id}`);
                // Update row data
                row.find('td').eq(0).text(updatedData.first_name);
                row.find('td').eq(1).text(updatedData.last_name);
                row.find('td').eq(2).text(updatedData.contact_info);
                row.find('td').eq(3).text(updatedData.age);
                row.find('td').eq(4).text(updatedData.sex);
                row.find('td').eq(5).text(updatedData.course);
                row.find('td').eq(6).text(updatedData.section);
                row.find('td').eq(7).text(updatedData.height);
                row.find('td').eq(8).text(updatedData.weight);
                row.find('td').eq(9).text(updatedData.bmi);

                alert(`Registration for student ID ${updatedData.student_id} updated.`);
                $('#editModal').modal('hide');
            } else {
                alert('Failed to update registration.');
            }
        }
    });
});


                // Show modal with form
                $('#editModal .modal-body').html(modalContent);
                const editModal = new bootstrap.Modal('#editModal');
                editModal.show();

                // Handle form submission
                $('#edit-form').submit(function (e) {
                    e.preventDefault();
                    const updatedData = Object.fromEntries(new FormData(this).entries());

                    // Simulate edit operation
                    fields.eq(0).text(updatedData.first_name);
                    fields.eq(1).text(updatedData.last_name);
                    fields.eq(2).text(updatedData.contact_info);
                    fields.eq(3).text(updatedData.age);
                    fields.eq(4).text(updatedData.sex);
                    fields.eq(5).text(updatedData.course);
                    fields.eq(6).text(updatedData.section);
                    fields.eq(7).text(updatedData.height);
                    fields.eq(8).text(updatedData.weight);
                    fields.eq(9).text(updatedData.bmi);

                    editModal.hide();
                    alert(`Registration for student ID ${userId} updated.`);
                });
            });
        });
    </script>

    <!-- Modal for Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Registration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
</body>

</html>