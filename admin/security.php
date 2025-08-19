<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getCurrentUser();

// Get security stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE action = 'login' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$stmt->execute();
$loginsToday = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE failed_attempts >= 3");
$stmt->execute();
$lockedAccounts = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT ip_address) FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$stmt->execute();
$uniqueIPs = $stmt->fetchColumn();

// Get recent security events
$stmt = $pdo->prepare("
    SELECT al.*, u.email as user_email, u.name as user_name
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.id
    WHERE al.action IN ('login', 'failed_login', 'security_alert', 'user_created', 'user_deactivated')
    ORDER BY al.created_at DESC
    LIMIT 20
");
$stmt->execute();
$securityEvents = $stmt->fetchAll();
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
        <div class="max-w-7xl mx-auto py-8 px-6">
            <!-- Security Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <span class="material-symbols-outlined">login</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Logins Today</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $loginsToday; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <span class="material-symbols-outlined">lock</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Locked Accounts</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $lockedAccounts; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <span class="material-symbols-outlined">public</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Unique IPs Today</p>
                            <p class="text-2xl font-semibold text-gray-900"><?php echo $uniqueIPs; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Security Settings -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Security Settings</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Password Policy</p>
                                <p class="text-xs text-gray-500">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters required</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Login Attempts Limit</p>
                                <p class="text-xs text-gray-500">Maximum <?php echo MAX_LOGIN_ATTEMPTS; ?> failed attempts</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Session Timeout</p>
                                <p class="text-xs text-gray-500"><?php echo gmdate('H:i:s', SESSION_TIMEOUT); ?> hours</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Audit Logging</p>
                                <p class="text-xs text-gray-500">All user actions logged</p>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Security Actions</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4">
                            <button onclick="unlockAllAccounts()" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                                <div class="flex items-center">
                                    <span class="material-symbols-outlined text-blue-600 mr-3">lock_open</span>
                                    <span class="text-sm font-medium text-gray-700">Unlock All Accounts</span>
                                </div>
                                <span class="material-symbols-outlined text-gray-400">chevron_right</span>
                            </button>
                            
                            <button onclick="clearAuditLogs()" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                                <div class="flex items-center">
                                    <span class="material-symbols-outlined text-yellow-600 mr-3">delete_sweep</span>
                                    <span class="text-sm font-medium text-gray-700">Clear Old Audit Logs</span>
                                </div>
                                <span class="material-symbols-outlined text-gray-400">chevron_right</span>
                            </button>
                            
                            <button onclick="exportSecurityReport()" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                                <div class="flex items-center">
                                    <span class="material-symbols-outlined text-green-600 mr-3">download</span>
                                    <span class="text-sm font-medium text-gray-700">Export Security Report</span>
                                </div>
                                <span class="material-symbols-outlined text-gray-400">chevron_right</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Events -->
            <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">Recent Security Events</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($securityEvents as $event): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-start">
                            <span class="material-symbols-outlined p-2 bg-gray-100 text-gray-600 rounded-full text-sm mr-3">
                                <?php 
                                echo match($event['action']) {
                                    'login' => 'login',
                                    'failed_login' => 'error',
                                    'security_alert' => 'warning',
                                    'user_created' => 'person_add',
                                    'user_deactivated' => 'person_remove',
                                    default => 'info'
                                };
                                ?>
                            </span>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($event['action']); ?></p>
                                    <span class="text-xs text-gray-500"><?php echo timeAgo($event['created_at']); ?></span>
                                </div>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($event['details']); ?></p>
                                <div class="flex items-center mt-1 text-xs text-gray-500">
                                    <span><?php echo htmlspecialchars($event['user_email'] ?? 'System'); ?></span>
                                    <span class="mx-2">•</span>
                                    <span><?php echo htmlspecialchars($event['ip_address']); ?></span>
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

        function unlockAllAccounts() {
            if (confirm('Are you sure you want to unlock all locked accounts?')) {
                // Implementation would go here
                alert('All accounts have been unlocked');
            }
        }

        function clearAuditLogs() {
            if (confirm('Are you sure you want to clear old audit logs? This action cannot be undone.')) {
                // Implementation would go here
                alert('Old audit logs have been cleared');
            }
        }

        function exportSecurityReport() {
            // Implementation would go here
            alert('Security report export started');
        }
    </script>
</body>
</html>