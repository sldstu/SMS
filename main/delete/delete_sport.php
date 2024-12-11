<?php
session_start();
if ($_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '../../database/database.class.php';
$conn = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sport_id = $data['sport_id'];

    $query = $conn->prepare("DELETE FROM sports WHERE sport_id = :sport_id");
    $query->bindParam(':sport_id', $sport_id);

    if ($query->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit();
}
?>
