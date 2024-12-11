<?php
session_start();
if ($_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '../../database/database.class.php';
$conn = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sport_name = $_POST['sport_name'];
    $sport_description = $_POST['sport_description'];

    // Handle file upload for sport image
    $sport_image = '';
    if (isset($_FILES['sport_image']) && $_FILES['sport_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['sport_image']['tmp_name'];
        $fileName = $_FILES['sport_image']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $uploadFileDir = '/path/to/your/upload/directory/'; // Update this path
            $dest_path = $uploadFileDir . $fileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $sport_image = $dest_path;
            } else {
                echo json_encode(['success' => false, 'message' => 'There was an error uploading the file.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions)]);
            exit();
        }
    }

    // Insert the new sport into the database
    $query = $conn->prepare("INSERT INTO sports (sport_name, sport_description, sport_image) VALUES (:sport_name, :sport_description, :sport_image)");
    $query->bindParam(':sport_name', $sport_name);
    $query->bindParam(':sport_description', $sport_description);
    $query->bindParam(':sport_image', $sport_image);

    if ($query->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit();
}
?>
