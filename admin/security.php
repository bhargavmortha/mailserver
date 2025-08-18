<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireAdmin();

$user = getCurrentUser();
$success = '';
$error = '';

// Get security logs
$stmt = $pdo->prepare("
    SELECT * FROM audit_logs 
    WHERE action IN ('login', 'failed_login', 'security_alert', 'password_change')
    ORDER BY created_at DESC 
    LIMIT 50
");
$stmt->execute();
$securityLogs = $stmt->fetchAll();

// Get failed login attempts
$stmt = $pdo->prepare("
    SELECT u.email, u.failed_attempts, u.last_attempt, u.is_active
    FROM users u
    WHERE u.failed_attempts > 0
    ORDER BY u.last_attempt DESC
");
$stmt->execute();
$failedAttempts = $stmt->fetchAll();

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'reset_attempts') {
        $userId = $_POST['user_id'];
        $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, last_attempt = NULL WHERE id = ?");
        if ($stmt->execute([$userId])) {
            $success = 'Failed login attempts reset successfully';
            logActivity($user['id'], 'security_action', "Reset failed attempts for user ID: $userId");
        } else {
            $error = 'Failed to reset login attempts';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Dashboard - MailFlow Admin</title>
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
                        <span class="font-semibold">Back to Admin</span>
                    </a>
                </div>
                <h1 class="text-xl font-semibold text-gray-800">Security Dashboard</h1>
                <div class="w-32"></div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="max-w-6xl mx-auto py-8 px-6">
            <?php if ($success): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <!-- Security Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <span class="material-symbols-outlined text-red-600">warning</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Failed Logins</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo count($failedAttempts); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <span class="material-symbols-outlined text-green-600">shield</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Security Level</p>
                            <p class="text-2xl font-bold text-gray-900">High</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <span class="material-symbols-outlined text-blue-600">lock</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Active Sessions</p>
                            <p class="text-2xl font-bold text-gray-900">1</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Failed Login Attempts -->
            <?php if (!empty($failedAttempts)): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">Failed Login Attempts</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Attempt</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($failedAttempts as $attempt): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($attempt['email']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $attempt['failed_attempts']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $attempt['last_attempt'] ? date('M j, Y g:i A', strtotime($attempt['last_attempt'])) : 'Never'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        <?php echo $attempt['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $attempt['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="reset_attempts">
                                        <input type="hidden" name="user_id" value="<?php echo $attempt['id']; ?>">
                                        <button type="submit" class="text-primary-600 hover:text-primary-900">
                                            Reset
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Security Logs -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">Security Activity Log</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($securityLogs as $log): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-start">
                            <span class="material-symbols-outlined p-1 bg-red-50 text-red-600 rounded-full text-sm mr-3">security</span>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($log['action']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($log['details']); ?></p>
                                <div class="flex items-center mt-1 text-xs text-gray-400">
                                    <span><?php echo timeAgo($log['created_at']); ?></span>
                                    <span class="mx-1">•</span>
                                    <span><?php echo htmlspecialchars($log['ip_address']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
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
    </script>
</body>
</html>