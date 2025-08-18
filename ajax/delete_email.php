<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$emailId = $input['email_id'] ?? null;

if (!$emailId) {
    echo json_encode(['success' => false, 'message' => 'Email ID required']);
    exit;
}

$user = getCurrentUser();

// Mark email as deleted
$stmt = $pdo->prepare("
    UPDATE emails 
    SET is_deleted = 1 
    WHERE id = ? AND (recipient_id = ? OR sender_id = ?)
");
$result = $stmt->execute([$emailId, $user['id'], $user['id']]);

echo json_encode(['success' => $result]);
?>