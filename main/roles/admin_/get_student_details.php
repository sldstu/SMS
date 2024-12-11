<?php
require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admin only.");
}


if (isset($_GET['registration_id'])) {
    $registration_id = $_GET['registration_id'];

    $query = $conn->prepare("
        SELECT 
            u.first_name,
            u.last_name,
            d.contact_info,
            d.birthday,
            d.address,
            d.sex,
            d.course,
            d.section,
            TO_BASE64(d.cor) as cor,
            TO_BASE64(d.id_image) as id_image,
            TO_BASE64(d.medcert) as medcert
        FROM registrations r
        JOIN users u ON r.student_id = u.user_id
        JOIN student_details d ON u.user_id = d.user_id
        WHERE r.registration_id = :registration_id
    ");
    $query->bindParam(':registration_id', $registration_id, PDO::PARAM_INT);
    $query->execute();
    $registration = $query->fetch(PDO::FETCH_ASSOC);

    if ($registration) {
        echo json_encode($registration);
    } else {
        echo json_encode(['error' => 'Registration not found']);
    }
}
?>
