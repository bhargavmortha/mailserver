<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAuth();

$user = getCurrentUser();
$emailId = $_GET['id'] ?? null;

if (!$emailId) {
    header('Location: index.php');
    exit;
}

// Get email details
$stmt = $pdo->prepare("
    SELECT e.*, 
           COALESCE(s.name, s.email) as sender_name,
           COALESCE(r.name, r.email) as recipient_name,
           s.email as sender_email,
           r.email as recipient_email
    FROM emails e
    LEFT JOIN users s ON e.sender_id = s.id
    LEFT JOIN users r ON e.recipient_id = r.id
    WHERE e.id = ? AND (e.recipient_id = ? OR e.sender_id = ?)
");
$stmt->execute([$emailId, $user['id'], $user['id']]);
$email = $stmt->fetch();

if (!$email) {
    header('Location: index.php');
    exit;
}

// Mark as read if user is recipient
if ($email['recipient_id'] == $user['id'] && !$email['is_read']) {
    markAsRead($emailId, $user['id']);
    $email['is_read'] = 1;
}

// Get attachments
$stmt = $pdo->prepare("SELECT * FROM email_attachments WHERE email_id = ?");
$stmt->execute([$emailId]);
$attachments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($email['subject']); ?> - MailFlow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
        @import url(https://fonts.googleapis.com/css2?family=Open+Sans&display=swap);
        @import url(https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined);
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 shadow-sm">
            <div class="flex items-center justify-between h-16 px-6">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center text-primary-600 hover:text-primary-700">
                        <span class="material-symbols-outlined mr-2">arrow_back</span>
                        <span class="font-semibold">Back to Inbox</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button onclick="toggleStar(<?php echo $email['id']; ?>)" 
                            class="p-2 rounded-md <?php echo $email['is_starred'] ? 'text-yellow-500' : 'text-gray-500'; ?> hover:bg-gray-100 transition duration-200">
                        <span class="material-symbols-outlined">star</span>
                    </button>
                    
                    <button onclick="deleteEmail(<?php echo $email['id']; ?>)" 
                            class="p-2 rounded-md text-gray-500 hover:bg-gray-100 hover:text-red-600 transition duration-200">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                    
                    <button onclick="window.location.href='compose.php?reply=<?php echo $email['id']; ?>'" 
                            class="p-2 rounded-md text-gray-500 hover:bg-gray-100 transition duration-200">
                        <span class="material-symbols-outlined">reply</span>
                    </button>
                    
                    <button onclick="window.location.href='compose.php?forward=<?php echo $email['id']; ?>'" 
                            class="p-2 rounded-md text-gray-500 hover:bg-gray-100 transition duration-200">
                        <span class="material-symbols-outlined">forward</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Email Content -->
        <div class="max-w-4xl mx-auto py-8 px-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <!-- Email Header -->
                <div class="p-6 border-b border-gray-200">
                    <h1 class="text-2xl font-semibold text-gray-900 mb-4">
                        <?php echo htmlspecialchars($email['subject']); ?>
                    </h1>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 mr-3">
                                <span class="material-symbols-outlined text-sm">person</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">
                                    <?php echo htmlspecialchars($email['sender_name']); ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($email['sender_email']); ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <p class="text-sm text-gray-500">
                                <?php echo date('M j, Y \a\t g:i A', strtotime($email['created_at'])); ?>
                            </p>
                            <p class="text-xs text-gray-400">
                                to <?php echo htmlspecialchars($email['recipient_email']); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                <?php if (!empty($attachments)): ?>
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Attachments</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($attachments as $attachment): ?>
                        <a href="download.php?id=<?php echo $attachment['id']; ?>" 
                           class="flex items-center px-3 py-2 bg-white border border-gray-200 rounded-md hover:bg-gray-50 transition duration-200">
                            <span class="material-symbols-outlined text-gray-500 mr-2 text-sm">attach_file</span>
                            <span class="text-sm text-gray-700"><?php echo htmlspecialchars($attachment['filename']); ?></span>
                            <span class="text-xs text-gray-500 ml-2">(<?php echo formatFileSize($attachment['size']); ?>)</span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Email Body -->
                <div class="p-6">
                    <div class="prose max-w-none">
                        <?php echo nl2br(htmlspecialchars($email['body'])); ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                    <div class="flex items-center space-x-3">
                        <button onclick="window.location.href='compose.php?reply=<?php echo $email['id']; ?>'" 
                                class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md font-medium transition duration-200 flex items-center">
                            <span class="material-symbols-outlined mr-2 text-sm">reply</span>
                            Reply
                        </button>
                        
                        <button onclick="window.location.href='compose.php?reply_all=<?php echo $email['id']; ?>'" 
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md font-medium transition duration-200 flex items-center">
                            <span class="material-symbols-outlined mr-2 text-sm">reply_all</span>
                            Reply All
                        </button>
                        
                        <button onclick="window.location.href='compose.php?forward=<?php echo $email['id']; ?>'" 
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md font-medium transition duration-200 flex items-center">
                            <span class="material-symbols-outlined mr-2 text-sm">forward</span>
                            Forward
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f3f1ff',
                            500: '#7341ff',
                            600: '#631bff',
                            700: '#611bf8'
                        }
                    }
                }
            }
        };

        function toggleStar(emailId) {
            fetch('ajax/toggle_star.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({email_id: emailId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        function deleteEmail(emailId) {
            if (confirm('Are you sure you want to delete this email?')) {
                fetch('ajax/delete_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({email_id: emailId})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'index.php';
                    }
                });
            }
        }
    </script>
</body>
</html>