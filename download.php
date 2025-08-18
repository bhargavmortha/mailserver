<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

requireAuth();

$user = getCurrentUser();
$attachmentId = $_GET['id'] ?? null;

if (!$attachmentId) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

// Get attachment details and verify user has access
$stmt = $pdo->prepare("
    SELECT ea.*, e.sender_id, e.recipient_id
    FROM email_attachments ea
    JOIN emails e ON ea.email_id = e.id
    WHERE ea.id = ? AND (e.sender_id = ? OR e.recipient_id = ?)
");
$stmt->execute([$attachmentId, $user['id'], $user['id']]);
$attachment = $stmt->fetch();

if (!$attachment) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

$filepath = UPLOAD_PATH . $attachment['filepath'];

if (!file_exists($filepath)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

// Set headers for file download
header('Content-Type: ' . ($attachment['mime_type'] ?: 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . $attachment['filename'] . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: private');

// Output file
readfile($filepath);
exit;
?>