<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getCurrentUser();
$systemStats = getSystemStats();
$auditLogs = getAuditLogs(10);
$recentEmails = getEmails($user['id'], 'inbox', 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MailFlow</title>
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
                    <a href="../index.php" class="flex items-center text-primary-600 hover:text-primary-700">
                        <span class="material-symbols-outlined mr-2">arrow_back</span>
                        <span class="font-semibold">Back to Dashboard</span>
                    </a>
                </div>
                <h1 class="text-xl font-semibold text-gray-800">Admin Dashboard</h1>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="../logout.php" class="text-sm text-red-600 hover:text-red-700">Logout</a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-8 px-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <span class="material-symbols-outlined text-blue-600">group</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Active Users</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $systemStats['active_users']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <span class="material-symbols-outlined text-green-600">email</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Mail Queue</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $systemStats['mail_queue']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <span class="material-symbols-outlined text-yellow-600">storage</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Storage Usage</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $systemStats['storage_usage']; ?>%</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <span class="material-symbols-outlined text-green-600">check_circle</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Server Status</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $systemStats['server_status']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Quick Actions</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4">
                            <a href="users.php" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                                <span class="material-symbols-outlined text-primary-600 mb-2">group</span>
                                <span class="text-sm font-medium text-gray-700">Manage Users</span>
                            </a>
                            <a href="security.php" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                                <span class="material-symbols-outlined text-primary-600 mb-2">security</span>
                                <span class="text-sm font-medium text-gray-700">Security</span>
                            </a>
                            <a href="reports.php" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                                <span class="material-symbols-outlined text-primary-600 mb-2">analytics</span>
                                <span class="text-sm font-medium text-gray-700">Reports</span>
                            </a>
                            <a href="settings.php" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-200">
                                <span class="material-symbols-outlined text-primary-600 mb-2">settings</span>
                                <span class="text-sm font-medium text-gray-700">Settings</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">System Health</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Database Connection</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                                Connected
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Mail Server</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                                Online
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">File System</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                                Accessible
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Session Handler</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                                Active
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Logs -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">Recent Activity</h2>
                    <a href="audit.php" class="text-sm text-primary-600 hover:text-primary-700">View All</a>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($auditLogs as $log): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-start">
                            <span class="material-symbols-outlined p-1 bg-<?php echo $log['color']; ?>-50 text-<?php echo $log['color']; ?>-600 rounded-full text-sm mr-3"><?php echo $log['icon']; ?></span>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($log['action']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($log['details']); ?></p>
                                <div class="flex items-center mt-1 text-xs text-gray-400">
                                    <span><?php echo timeAgo($log['created_at']); ?></span>
                                    <span class="mx-1">•</span>
                                    <span><?php echo htmlspecialchars($log['user_email'] ?? 'System'); ?></span>
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