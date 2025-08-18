<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_POST && isset($_POST['auto_save'])) {
    $user = getCurrentUser();
    $recipientEmail = trim($_POST['recipient']);
    $subject = trim($_POST['subject']);
    $body = $_POST['body'];
    
    if (!empty($recipientEmail) && !empty($subject) && !empty($body)) {
        // Find recipient
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$recipientEmail]);
        $recipient = $stmt->fetch();
        
        if ($recipient) {
            // Save as draft
            $stmt = $pdo->prepare("
                INSERT INTO emails (sender_id, recipient_id, subject, body, is_draft, created_at)
                VALUES (?, ?, ?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE
                subject = VALUES(subject),
                body = VALUES(body),
                updated_at = NOW()
            ");
            $result = $stmt->execute([$user['id'], $recipient['id'], $subject, $body]);
            
            echo json_encode(['success' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Recipient not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>