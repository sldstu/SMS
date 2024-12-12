<?php
require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'];
$action = $input['action'];

$status = ($action === 'activate') ? 1 : 0;

$query = $conn->prepare("UPDATE users SET is_active = :status WHERE user_id = :user_id");
$query->bindParam(':status', $status);
$query->bindParam(':user_id', $user_id);

if ($query->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update status']);
}
