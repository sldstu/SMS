<?php
require_once '../MAIN/database/database.class.php';
require_once '../MAIN/includes/clean_function.php';

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
$registrationErr = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'sport_id'     => $_POST['sport_id'] ?? null,
        'last_name'    => clean_input($_POST['last_name'] ?? ''),
        'first_name'   => clean_input($_POST['first_name'] ?? ''),
        'contact_info' => clean_input($_POST['contact_info'] ?? ''),
        'age'          => clean_input($_POST['age'] ?? ''),
        'height'       => clean_input($_POST['height'] ?? ''),
        'weight'       => clean_input($_POST['weight'] ?? ''),
        'bmi'          => clean_input($_POST['bmi'] ?? ''),
        'medcert'      => '', // File upload handled separately
        'cor_pic'    => '',
        'id_pic'      => '',
        'sex'          => clean_input($_POST['sex'] ?? ''),
        'course'       => clean_input($_POST['course'] ?? ''),
        'section'      => clean_input($_POST['section'] ?? ''),
    ];

    // Validate required fields
    foreach ($formData as $key => $value) {
        if (empty($value) && !in_array($key, ['bmi', 'medcert', 'cor_pic', 'id_pic'])) {
            $registrationErr = ucfirst($key) . " is required.";
            break;
        }
    }

    // File Upload Handling
    $uploadDir = "../uploads/";
    foreach (['medcert', 'cor_pic', 'id_pic'] as $fileKey) {
        if (!empty($_FILES[$fileKey]['name'])) {
            $fileTmpName = $_FILES[$fileKey]['tmp_name'];
            $fileName = basename($_FILES[$fileKey]['name']);
            $targetPath = $uploadDir . $fileKey . '/' . $fileName;

            if (!file_exists(dirname($targetPath))) {
                mkdir(dirname($targetPath), 0777, true);
            }

            if (move_uploaded_file($fileTmpName, $targetPath)) {
                $formData[$fileKey] = $targetPath;
            } else {
                $registrationErr = "Failed to upload $fileKey.";
                break;
            }
        }
    }

    // Insert registration if no errors
    if (!$registrationErr) {
        $query = $conn->prepare("
            INSERT INTO registrations 
            (student_id, sport_id, last_name, first_name, contact_info, age, height, weight, bmi, medcert, cor_pic, id_pic, sex, course, section)
            VALUES
            (:student_id, :sport_id, :last_name, :first_name, :contact_info, :age, :height, :weight, :bmi, :medcert, :cor_pic, :id_pic, :sex, :course, :section)
        ");

        $query->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        foreach ($formData as $key => $value) {
            $query->bindValue(":$key", $value);
        }

        if ($query->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Registration successful.']);
            exit();
        } else {
            $registrationErr = 'Database error. Please try again.';
        }
    }
}

// Fetch registered sports
$query = $conn->prepare("
    SELECT s.sport_name, r.status 
    FROM sports s 
    JOIN registrations r ON s.sport_id = r.sport_id 
    WHERE r.student_id = :student_id
");
$query->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$query->execute();
$registrations = $query->fetchAll(PDO::FETCH_ASSOC);

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
                        <button class="btn btn-primary" onclick="showRegistrationForm(<?= $sport['sport_id'] ?>)">Register</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registrationModalLabel">Register for Sport</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <!-- Form fields (last name, first name, etc.) as in your original code -->
          <form action="" id="registrationForm" method="post"  enctype="multipart/form-data">
          <input type="hidden" id="sport_id" name="sport_id">
          <!-- LAST NAME -->
          <div class="mb-3">
            <label for="last_name" class="form-label" >Last Name</label><span class="error">*</span><br>
            <input type="text" id="last_name" name="last_name" class="form-control" >
          </div>  
          <!-- FIRST NAME -->
          <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label><span class="error">*</span><br>
            <input type="text" id="first_name" name="first_name" class="form-control" >
          </div>
          <!-- CONTACT INFORMATION -->
          <div class="mb-3">
            <label for="contact_info" class="form-label">Contact Info</label><span class="error">*</span><br>
            <input type="text" id="contact_info" name="contact_info" class="form-control" >
          </div>
          <!-- AGE -->
          <div class="mb-3">
            <label for="age" class="form-label">Age</label><span class="error">*</span><br>
            <input type="number" id="age" name="age" class="form-control">
          </div>
          <!-- HEIGHT -->
          <div class="mb-3">
            <label for="height" class="form-label">Height (in cm)</label><span class="error">*</span><br>
            <input type="text" id="height" name="height" class="form-control" placeholder="in cm">
          </div>
          <!-- WEIGHT -->
          <div class="mb-3">
            <label for="weight" class="form-label">Weight (in kg)</label><span class="error">*</span><br>
            <input type="text" name="weight" id="weight" class="form-control" placeholder="in kg">
          </div>
          <div class="container">
          <div class="container">
          <!-- BMI -->
          <div class="mb-3">
            <label for="bmi" class="form-label">BMI</label><br>
            <input type="text" id="bmi" name="bmi" class="form-control" readonly>
          </div>


          <div class="container">
           <div class="row">
           <!-- Medical Certificate -->
          <div class="col-4 mb-3">
            <label for="medcert" class="form-label">Medical Certificate</label><br>
            <div class="input-group">
                <label for="medcert" class="input-group-text d-flex align-items-center justify-content-center" style="font-size: 30px; height: 50px;">
                    <i class="fas fa-file-medical"></i>
                </label>
                <input type="file" name="medcert" id="medcert" class="form-control" style="display:none;">
            </div>
          </div>

           <!-- Certificate of Registration -->
          <div class="col-4 mb-3">
            <label for="cor_pic" class="form-label">Certificate of Registration</label>
            <div class="input-group">
              <label for="cor_pic" class="input-group-text d-flex align-items-center justify-content-center" style="font-size: 30px; height: 50px;">
                  <i class="fas fa-certificate"></i>
                </label>
              <input type="file" id="cor_pic" name="cor_pic" class="form-control" style="display:none;">
            </div>
          </div>
  
        <!-- ID Picture -->
        <div class="col-4 mb-3">
          <label for="id_pic" class="form-label">ID Picture (front and back) </label>
          <div class="input-group">
            <label for="id_pic" class="input-group-text d-flex align-items-center justify-content-center" style="font-size: 30px; height: 50px;">
              <i class="fas fa-id-card"></i>
             </label>
            <input type="file" id="id_pic" name="id_pic" class="form-control" placeholder="front and back" style="display:none;">
              </div>
            </div>
        </div>
        <!-- SEX -->
          <div class="mb-3">
            <label for="sex" class="form-label">Sex</label>
            <select id="sex" name="sex" class="form-select" >
              <option selected>Open this select menu</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
          <!-- COURSE -->
          <div class="mb-3">
            <label for="course" class="form-label">Course</label>
            <select id="course" name="course" class="form-select" onchange="updateSections()">
              <option selected>Open this select menu</option>
              <option value="CS">CS</option>
              <option value="IT">IT</option>
              <option value="ACT">ACT</option>
            </select>
          </div>
          <!-- SECTION -->
          <div class="mb-3">
            <label for="section" class="form-label">Section</label>
            <select id="section" name="section" class="form-select">
            </select>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </form>

            </div>
        </div>
    </div>
</div>

<script>
  // Function to calculate BMI
function calculateBMI() {
    var height = parseFloat(document.getElementById('height').value); // height in cm
    var weight = parseFloat(document.getElementById('weight').value); // weight in kg

    if (height > 0 && weight > 0) {
        // Convert height to meters
        height = height / 100;
        
        // Calculate BMI
        var bmi = weight / (height * height);
        
        // Display BMI value
        document.getElementById('bmi').value = bmi.toFixed(2); // Round to 2 decimal places
    } else {
        // Clear BMI field if invalid input
        document.getElementById('bmi').value = '';
    }
}

// Add event listeners for height and weight inputs
document.getElementById('height').addEventListener('input', calculateBMI);
document.getElementById('weight').addEventListener('input', calculateBMI);

   //function for section
function updateSections() {
            var course = document.getElementById('course').value;
            var section = document.getElementById('section');
            section.innerHTML = '';
            if (course === 'CS' || course === 'IT') {
                var options = ['1A', '2A', '2B', '2C', '3A', '3B', '4A'];
                for (var i = 0; i < options.length; i++) {
                    var option = document.createElement('option');
                    option.value = options[i];
                    option.text = options[i];
                    section.appendChild(option);
                }
            } else {
                var option = document.createElement('option');
                option.value = '';
                option.text = 'N/A';
                section.appendChild(option);
            }
        }

        function showRegistrationForm(sport_id) {
            document.getElementById('sport_id').value = sport_id;
            document.getElementById('registration_form').style.display = 'block';
        }

        function showSection(sectionId) {
            var sections = document.getElementsByClassName('dashboard-section');
            for (var i = 0; i < sections.length; i++) {
                sections[i].style.display = 'none';
            }
            document.getElementById(sectionId).style.display = 'block';
        }
// search bar
// Filter sports by name
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

function showRegistrationForm(sportId) {
    // Set sport ID for registration
    $('#sport_id').val(sportId);
    $('#registration_form').show();
}


function showRegistrationForm(sport_id) {
    // Set the sport_id value to the hidden input in the form
    document.getElementById('sport_id').value = sport_id;
    
    // Show the modal
    var modal = new bootstrap.Modal(document.getElementById('registrationModal'));
    modal.show();
}


</script>
</body>
</html>
